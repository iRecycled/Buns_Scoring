<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Session extends Model
{
    use HasFactory;
    public function league() {
        return $this->belongsTo(League::class, 'league_id', 'leagueId');
    }

    public function season() {
        return $this->belongsTo(Season::class);
    }

    public function getActualIntervalAttribute() {
        if (Str::contains($this->interval, 'laps')){
            return $this->interval;
        }
        if (Str::contains($this->interval, '-')){
            $this->interval = 0;
        }
        if(Str::contains($this->interval, ':')){
            //1:26.134
            $minutes = Str::before($this->interval, ':');
            $this->interval = Str::after($this->interval, ':')+($minutes * 60);
        }
        return $this->interval + $this->penalty_seconds;
    }

    public function scopeFastestLap($query, $sessionId, $simsessionName) {
        return $query->where('subsession_id', $sessionId)
            ->where('simsession_name', $simsessionName)
            ->where('best_lap_time', '<>', '-')
            ->orderBy('best_lap_time')
            ->first();
    }
}
