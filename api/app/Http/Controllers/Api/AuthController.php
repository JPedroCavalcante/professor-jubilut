<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->only('email', 'password'));

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json([
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'user' => new UserResource($result['user']),
        ]);
    }

    public function logout()
    {
        $this->authService->logout();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me()
    {
        return new UserResource(auth()->user());
    }
}
