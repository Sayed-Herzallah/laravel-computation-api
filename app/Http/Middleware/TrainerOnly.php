<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrainerOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()?->role !== 'trainer') {
            return response()->json([
                'success' => false,
                'message' => 'هذه العملية متاحة للمدربين فقط',
            ], 403);
        }
        return $next($request);
    }
}
