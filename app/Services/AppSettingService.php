<?php

namespace App\Services;

use App\Models\AppSetting;

class AppSettingService
{
    // ===================== جلب كل الإعدادات =====================
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return AppSetting::orderBy('operation_type')->orderBy('level')->get();
    }

    // ===================== حفظ / تحديث إعداد =====================
    public function upsert(array $data): AppSetting
    {
        return AppSetting::updateOrCreate(
            [
                'operation_type' => $data['operation_type'],
                'level'          => $data['level'],
            ],
            [
                'questions_count'  => $data['questions_count'],
                'duration_minutes' => $data['duration_minutes'],
            ]
        );
    }

    // ===================== جلب إعداد عملية معينة =====================
    public function getFor(string $operation, int $level): ?AppSetting
    {
        return AppSetting::getFor($operation, $level);
    }
}
