<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;
    public function league(){
        return $this->belongsTo(League::class, 'league_id', 'leagueId');
    }
    public function season(){
        return $this->hasMany(Session::class, 'id', 'season_id');
    }
    public function scorings(){
        return $this->hasMany(Scoring::class, 'season_id', 'id');
    }
}
