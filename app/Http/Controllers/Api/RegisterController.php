<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

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
        $response = $this->validateRequest($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password' => ['required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image'],
            'phone' => ['nullable', 'string']
        ]);

        if ($response['status'] === ResponseDefined::SUCCESS) {
            $response = $this->userService->register($request->all());
        }

        return $response;
    }
}