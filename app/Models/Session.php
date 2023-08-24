<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    use HasFactory;
    public function league() {
        return $this->belongsTo(League::class, 'league_id', 'leagueId');
    }

    public function season() {
        return $this->belongsTo(Season::class);
    }
}
