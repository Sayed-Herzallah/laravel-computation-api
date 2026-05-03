<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(protected AuthService $authService) {}

    // POST /api/auth/login
    public function login(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'national_id' => 'required|string',
            'password'    => 'required|string',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $result = $this->authService->login($request->national_id, $request->password);
            return $this->success($result, 'تم تسجيل الدخول بنجاح');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    // POST /api/auth/logout
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return $this->success(null, 'تم تسجيل الخروج');
        } catch (\Exception $e) {
            return $this->error('فشل تسجيل الخروج', 500);
        }
    }
}
