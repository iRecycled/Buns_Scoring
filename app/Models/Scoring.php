<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scoring extends Model
{
    protected $table = 'scoring';
    use HasFactory;
    public function season(){
        $this->belongsTo(Season::class, 'season_id', 'id');
    }
}
