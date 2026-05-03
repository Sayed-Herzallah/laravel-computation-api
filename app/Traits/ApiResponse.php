<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, string $message = 'تم بنجاح', int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'result' => $data], $code);
    }

    protected function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        $res = ['success' => false, 'message' => $message];
        if ($errors) $res['errors'] = $errors;
        return response()->json($res, $code);
    }
}
