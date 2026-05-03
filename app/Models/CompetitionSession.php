<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionSession extends Model
{
    protected $fillable = [
        'competition_id', 'student_id', 'operation_type', 'level',
        'num1', 'num2', 'num3', 'correct_result',
        'student_answer', 'is_correct', 'time_taken_seconds'
    ];

    protected $casts = ['is_correct' => 'boolean'];

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
