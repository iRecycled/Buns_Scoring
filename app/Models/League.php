<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use HasFactory;
    public function seasons(){
        return $this->hasMany(Season::class, 'league_id', 'leagueId');
    }

    public function scorings(){
        return $this->hasMany(Scoring::class, 'league_id', 'leagueId');
    }
}
