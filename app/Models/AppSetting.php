<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['operation_type', 'level', 'questions_count', 'duration_minutes'];

    // جلب إعداد معين
    public static function getFor(string $operation, int $level): ?self
    {
        return self::where('operation_type', $operation)->where('level', $level)->first();
    }
}
