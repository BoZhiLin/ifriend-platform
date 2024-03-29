<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use App\Events\UserVerified;
use App\Events\UserMatched;
use App\Events\UserMatchInvite;

use App\Jobs\SendVerifyMail;
use App\Jobs\SendForgotMail;

use App\Models\User;

use App\Defined\CoinDefined;
use App\Defined\SystemDefined;
use App\Defined\VipTypeDefined;
use App\Defined\ResponseDefined;
use App\Defined\OrderTypeDefined;
use App\Defined\OrderStatusDefined;
use App\Defined\TransactionDefined;

use App\Repositories\VipRepository;
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserMatchRepository;
use App\Repositories\WalletRepository;
use App\Repositories\TransactionRepository;

class UserService extends Service
{
    protected $vipRepo;
    protected $userRepo;
    protected $orderRepo;
    protected $userMatchRepo;
    protected $walletRepo;
    protected $transactionRepo;

    public function __construct(
        VipRepository $vipRepo,
        UserRepository $userRepo,
        OrderRepository $orderRepo,
        UserMatchRepository $userMatchRepo,
        WalletRepository $walletRepo,
        TransactionRepository $transactionRepo
    ) {
        $this->vipRepo = $vipRepo;
        $this->userRepo = $userRepo;
        $this->orderRepo = $orderRepo;
        $this->userMatchRepo = $userMatchRepo;
        $this->walletRepo = $walletRepo;
        $this->transactionRepo = $transactionRepo;
    }
    
    /**
     * 註冊
     * 
     * @param array $data
     * @return array
     */
    public function register(array $data)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        $user = $this->userRepo->create($data);
        $response['data']['user'] = $user;

        return $response;
    }

    /**
     * 產生驗證碼並且寄Email
     * 
     * @param User $user
     * @return array
     */
    public function sendVerifyCode(User $user)
    {
        $response = ['status' => ResponseDefined::SUCCESS];

        if ($user->is_verified) {
            $response['status'] = ResponseDefined::VERIFY_HAS_PASSED;
        } else {
            $verify_code = Str::random(6);
            $key = "verify@user:$user->id";
            $expire_seconds = SystemDefined::VERIFY_CODE_EXPIRED * 60;
            Cache::put($key, $verify_code, $expire_seconds);
            SendVerifyMail::dispatch($user, $verify_code);
        }

        return $response;
    }

    /**
     * 驗證使用者信箱，驗證成功則贈送3天黃金會員
     * 
     * @param User $user
     * @param mixed $code
     * @return array
     */
    public function verifyUser(User $user, $code = null)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        $key = "verify@user:$user->id";
        $verify_code = Cache::get($key);

        if ($user->is_verified) {
            $response['status'] = ResponseDefined::VERIFY_HAS_PASSED;
        } elseif (!$code) {
            $response['status'] = ResponseDefined::VERIFY_CODE_REQUIRED;
        } elseif (!$verify_code) {
            $response['status'] = ResponseDefined::VERIFY_CODE_EXPIRED;
        } elseif ($code != $verify_code) {
            $response['status'] = ResponseDefined::VERIFY_CODE_ERROR;
        } elseif ($user->is_verified) {
            $response['status'] = ResponseDefined::VERIFY_HAS_PASSED;
        } else {
            $user->email_verified_at = now();
            $user->is_verified = true;
            $user->save();

            $this->vipRepo->buyByUser($user->id, VipTypeDefined::GOLD, SystemDefined::USER_DEFAULT_DAYS);
            Cache::forget($key);

            /** 通知中心 新增VIP試用通知 */
            event(new UserVerified($user));
        }

        return $response;
    }

    /**
     * 忘記密碼
     * 
     * @param string $email
     * @return array
     */
    public function forgetPassword(string $email)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        $user = $this->userRepo->getByEmail($email);

        if (is_null($user)) {
            $response['status'] = ResponseDefined::USER_NOT_FOUND;
        } else {
            $new_password = Str::random(10);
            $user->password = Hash::make($new_password);
            $user->save();
            SendForgotMail::dispatch($user, ['password' => $new_password]);
        }

        return $response;
    }

    /**
     * 重設密碼
     * 
     * @param int $user_id
     * @param string $password
     * @return array
     */
    public function resetPassword(int $user_id, string $password)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        $user = $this->userRepo->find($user_id);
        $user->password = Hash::make($password);
        $user->save();

        return $response;
    }

    /**
     * 取得使用者資料
     * 
     * @param int $user_id
     * @return array
     */
    public function getInfo(int $user_id)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        $response['data']['user'] = $this->getUserInfo($user_id);
        return $response;
    }

    /**
     * 設定使用者資料
     * 
     * @param int $user_id
     * @param array $data
     * @return array
     */
    public function setInfo(int $user_id, array $data)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        
        if (isset($data['avatar'])) {
            $new_avatar_path = 'avatar/' . $user_id . '.' . $data['avatar']->guessClientExtension();
            Storage::disk('public')->delete('avatar/' . $user_id);
            Storage::disk('public')->put($new_avatar_path, file_get_contents($data['avatar']->getRealPath()));
            $data['avatar'] = $new_avatar_path;
        }

        $user = $this->userRepo->update($user_id, $data);
        $response['data']['user'] = $this->getUserInfo($user->id);
        return $response;
    }

    /**
     * 發送配對LIKE or 接受LIKE邀請 (按LIKE，互相按LIKE則自動配對)
     * 
     * @param int $from_id
     * @param int $match_id
     * @return array
     */
    public function match(int $from_id, int $match_id)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        $wallet = $this->walletRepo->getByUser($from_id, CoinDefined::DAY_LIKE);

        if (!$this->userRepo->find($match_id)) {
            $response['status'] = ResponseDefined::USER_NOT_FOUND;
        } elseif ($from_id === $match_id) {
            $response['status'] = ResponseDefined::NOT_ALLOW_SEND_SELF;
        } elseif (
            ($match_info = $this->userMatchRepo->getByBothUser($from_id, $match_id)) &&
            $match_info->is_matched
        ) {
            $response['status'] = ResponseDefined::USER_HAS_MATCHED;
        } elseif (
            ($match_info = $this->userMatchRepo->getByBothUser($from_id, $match_id)) &&
            $match_info->from_id === $from_id
        ) {
            $response['status'] = ResponseDefined::MATCH_HAS_SEND;
        } elseif ($wallet->balance_available < 1) {
            $response['status'] = ResponseDefined::BALANCE_NOT_ENOUGH;
        } else {
            $this->transactionRepo->createByWallet($wallet, TransactionDefined::SEND_LIKE, -1);
            
            $match_info = $this->userMatchRepo->sendOrMatch([
                'from_id' => $from_id,
                'match_id' => $match_id
            ]);

            if ($match_info->is_matched) {
                event(new UserMatched($from_id, $match_id));
            } else {
                event(new UserMatchInvite($from_id, $match_id));
            }
        }

        return $response;
    }

    /**
     * 解除配對 or 拒絕配對邀請
     * 
     * @param int $from_id
     * @param int $target_id (配對對象 or 欲拒絕對象)
     * @return array
     */
    public function removeMatch(int $from_id, int $target_id)
    {
        $response = ['status' => ResponseDefined::SUCCESS];

        if (!$this->userRepo->find($target_id)) {
            $response['status'] = ResponseDefined::USER_NOT_FOUND;
        } elseif ($from_id === $target_id) {
            $response['status'] = ResponseDefined::NOT_ALLOW_REMOVE_SELF;
        } elseif (! $match_info = $this->userMatchRepo->getByBothUser($from_id, $target_id)) {
            $response['status'] = ResponseDefined::MATCH_NOT_FOUND;
        } else {
            $match_info->forceDelete();
        }

        return $response;
    }

    /**
     * 購買VIP資格
     * 
     * @param User $user
     * @return array
     */
    public function buyVIP(User $user)
    {
        $response = ['status' => ResponseDefined::SUCCESS];
        $order_info = [
            'user_id' => $user->id,
            'type' => OrderTypeDefined::BUY_GOLD_VIP,
            'amount' => 1,
            'value' => SystemDefined::GOLD_VIP_PRICE,
            'status' => OrderStatusDefined::PAYING
        ];

        $order = $this->orderRepo->create($order_info);
        $this->vipRepo->buyByUser($user->id, VipTypeDefined::GOLD, SystemDefined::VIP_EXPIRES_DAYS);
        /** 串金流 TODO */

        return $response;
    }

    /**
     * 回傳user陣列 (私有方法)
     * 
     * @param int $user_id
     * @return array
     */
    private function getUserInfo(int $user_id)
    {
        $user = $this->userRepo->find($user_id);
        $vip_type = $this->userRepo->getVipLevel($user);
        $like_posts = $user->favoritePosts->pluck('id');
        
        return [
            'id' => $user->id,
            'gender' => $user->gender,
            'name' => $user->name,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'introduction' => $user->introduction,
            'blood' => $user->blood,
            'constellation' => $user->constellation,
            'vip_type' => $vip_type,
            'is_verified' => $user->is_verified,
            'like_posts' => $like_posts
        ];
    }
}
