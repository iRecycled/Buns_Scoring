<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scoring extends Model
{
    use HasFactory;
    public function season(){
        $this->belongsTo(Season::class, 'season_id', 'id');
    }
}
