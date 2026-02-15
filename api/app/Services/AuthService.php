<?php

namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use Exception; // Added this import for the Exception class

class AuthService
{
    public function login(array $credentials)
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new Exception('Credenciais inválidas.', 401);
            }
        } catch (JWTException $e) {
            return ['error' => 'Could not create token.', 'status' => 500];
        }

        return ['token' => $token, 'user' => Auth::user()];
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
