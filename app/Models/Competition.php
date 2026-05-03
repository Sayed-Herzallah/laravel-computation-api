<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ===================== Competition =====================
class Competition extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'duration_minutes'];
    protected $casts    = ['start_time' => 'datetime', 'end_time' => 'datetime'];

    public function sessions()
    {
        return $this->hasMany(CompetitionSession::class, 'competition_id');
    }

    public function isActive(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }
}
