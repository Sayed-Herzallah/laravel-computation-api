<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'operation_type', 'level', 'rows_count',
        'num1', 'num2', 'num3', 'correct_result'
    ];

    // توليد الناتج الصحيح حسب نوع العملية
    public static function calculateResult(string $type, int $num1, int $num2, ?int $num3 = null): int
    {
        return match($type) {
            'addition'       => $num1 + $num2 + ($num3 ?? 0),
            'subtraction'    => $num1 - $num2 - ($num3 ?? 0),
            'multiplication' => $num1 * $num2,
            'division'       => $num2 != 0 ? intdiv($num1, $num2) : 0,
            'mixed'          => $num1 + $num2 - ($num3 ?? 0), // جمع وطرح
            default          => 0,
        };
    }

    // التحقق من صحة القسمة (لا يقبل كسور)
    public static function isValidDivision(int $num1, int $num2): bool
    {
        return $num2 != 0 && $num1 % $num2 === 0;
    }
}
