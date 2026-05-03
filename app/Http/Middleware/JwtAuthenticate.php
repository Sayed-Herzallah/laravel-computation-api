<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['success' => false, 'message' => 'انتهت صلاحية التوكن'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['success' => false, 'message' => 'توكن غير صالح'], 401);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'message' => 'يرجى تسجيل الدخول'], 401);
        }

        return $next($request);
    }
}
