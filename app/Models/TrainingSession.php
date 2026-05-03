<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $fillable = [
        'student_id', 'operation_type', 'level', 'questions_count',
        'rows_count', 'num1', 'num2', 'num3',
        'student_answer', 'correct_result', 'is_correct',
        'started_at', 'ended_at'
    ];

    protected $casts = [
        'is_correct'  => 'boolean',
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
