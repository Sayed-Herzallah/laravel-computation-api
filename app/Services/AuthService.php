<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(string $nationalId, string $password): array
    {
        $user = User::where('national_id', $nationalId)->first();

        if (!$user)                              throw new \Exception('المستخدم غير موجود', 404);
        if (!Hash::check($password, $user->password)) throw new \Exception('كلمة المرور غلط', 400);

        $token = JWTAuth::fromUser($user);

        return [
            'token'     => $token,
            'token_type'=> 'bearer',
            'user'      => [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ];
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
