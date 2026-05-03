<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLevel extends Model
{
    protected $fillable = [
        'student_id', 'operation_type', 'level',
        'total_questions', 'correct_answers', 'success_rate'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    // تحديث إحصائيات الطالب بعد كل إجابة
    public static function updateStats(int $studentId, string $operation, int $level, bool $isCorrect): void
    {
        $record = self::firstOrCreate(
            ['student_id' => $studentId, 'operation_type' => $operation, 'level' => $level],
            ['total_questions' => 0, 'correct_answers' => 0, 'success_rate' => 0]
        );

        $record->total_questions++;
        if ($isCorrect) $record->correct_answers++;
        $record->success_rate = round(($record->correct_answers / $record->total_questions) * 100, 2);
        $record->save();
    }
}
