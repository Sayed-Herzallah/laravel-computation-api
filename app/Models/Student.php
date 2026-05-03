<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['user_id', 'parent_id', 'name', 'national_id', 'level'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class, 'student_id');
    }

    public function competitionSessions()
    {
        return $this->hasMany(CompetitionSession::class, 'student_id');
    }

    public function levels()
    {
        return $this->hasMany(StudentLevel::class, 'student_id');
    }
}
