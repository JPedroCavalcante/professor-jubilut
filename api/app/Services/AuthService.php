<?php

namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials)
    {
        try {
            $token = JWTAuth::attempt($credentials);
        } catch (JWTException $e) {
            return ['error' => 'Could not create token.', 'status' => 500];
        }

        if (!$token) {
            return null;
        }

        return ['token' => $token, 'user' => Auth::user()];
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            // Token already invalid; logout proceeds silently.
        }
    }
}
