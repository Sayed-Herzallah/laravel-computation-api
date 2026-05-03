<?php

namespace App\Http\Controllers;

use App\Services\AppSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AppSettingController extends Controller
{
    use ApiResponse;

    public function __construct(protected AppSettingService $settingService) {}

    // GET /api/settings
    public function index(): JsonResponse
    {
        try {
            return $this->success($this->settingService->getAll(), 'تم جلب الإعدادات');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/settings  [trainer only]
    public function upsert(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'operation_type'  => 'required|in:addition,subtraction,multiplication,division,mixed',
            'level'           => 'required|integer|min:1|max:3',
            'questions_count' => 'required|integer|min:1|max:100',
            'duration_minutes'=> 'required|integer|min:1|max:60',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $setting = $this->settingService->upsert($request->all());
            return $this->success($setting, 'تم حفظ الإعدادات');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
