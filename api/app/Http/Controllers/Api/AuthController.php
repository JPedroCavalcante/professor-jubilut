<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Student;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token.'], 500);
        }

        $user = auth()->user();
        $studentId = null;
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'user'         => [
                'id'         => $user->id,
                'student_id' => $studentId,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
            ],
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me()
    {
        $user = auth()->user();
        $studentId = null;
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
        }

        return response()->json([
            'id'         => $user->id,
            'student_id' => $studentId,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
        ]);
    }
}
