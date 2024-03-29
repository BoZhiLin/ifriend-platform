<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Hash;

use App\Defined\VipTypeDefined;

use App\Models\User;

use App\Tools\Tool;

class UserRepository extends Repository
{
    /**
     * 新增/註冊會員
     */
    public function create(array $data)
    {
        $model = $this->getModel();
        $user = new $model();
        $user->gender = $data['gender'];
        $user->name = $data['name'];
        $user->birthday = $data['birthday'];
        $user->nickname = $data['nickname'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->constellation = Tool::getConstellation($data['birthday']);
        $user->age = Tool::getAge($data['birthday']);
        $user->save();

        return $user;
    }

    /**
     * 更新個人資料
     */
    public function update(int $id, array $data)
    {
        $user = $this->getModel()::find($id);
        
        foreach ($data as $attribute => $value) {
            $user->$attribute = $value;
        }

        $user->save();
        return $user;
    }

    public function getByEmail(string $email)
    {
        return $this->getModel()::where('email', $email)->first();
    }

    /**
     * 獲取VIP階級
     * 
     * @param User $user
     * @return string
     */
    public function getVipLevel(User $user)
    {
        $now = now();
        $user->load('vip');
        $vip = $user->vip;
        $vip_type = VipTypeDefined::VISITOR;

        if (!is_null($vip)) {
            if ($vip->expired_at > $now) {
                $vip_type = VipTypeDefined::GOLD;
            }
        }

        return $vip_type;
    }

    // public function getVipLevel(User $user)
    // {
    //     $now = now();
    //     $user->load('vips');
    //     $vips = $user->vips;
    //     $types = VipTypeDefined::all();
    //     $vip_type = VipTypeDefined::VISITOR;

    //     if (!$vips->isEmpty()) {
    //         foreach ($types as $type) {
    //             $current_vip = $vips->where('type', $type)
    //                 ->where('expired_at', '>', $now)
    //                 ->first();

    //             if (!is_null($current_vip)) {
    //                 $vip_type = $type;
                    
    //                 if ($type === VipTypeDefined::GOLD) {
    //                     break;
    //                 }
    //             }
    //         }
    //     }

    //     return $vip_type;
    // }

    public function getModel()
    {
        return User::class;
    }
}
