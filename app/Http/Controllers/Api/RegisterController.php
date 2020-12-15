<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Defined\GenderDefined;
use App\Defined\ResponseDefined;

use App\Services\UserService;

class RegisterController extends ApiController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function register(Request $request)
    {
        $genders = implode(',', GenderDefined::all());
        $response = $this->validateRequest($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', "in:$genders"],
            'nickname' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password' => ['required', 'string', 'min:8']
        ]);

        if ($response['status'] === ResponseDefined::SUCCESS) {
            $response = $this->userService->register($request->only([
                'name',
                'gender',
                'nickname',
                'email',
                'password'
            ]));
        }

        return $response;
    }
}
