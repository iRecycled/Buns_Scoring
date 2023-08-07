<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use Illuminate\Support\Facades\DB;

class ScoringController extends Controller
{
    public function createScoring(Request $request) {
        if($request){
            $score = new Scoring();
            $score->season_name = $request->season_name;
            $score->league_id = $request->leagueId;
            $score->position = $request->position;
            $score->points = $request->points;
        }

        try {
            $score->save();
        } 
        catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function calculateScoringForSingleSession($league_id, $season_id, $session_id) {
        $scoringRules = DB::table('scoring')->where('league_id', $league_id)
        ->where('season_id', $season_id)
        ->orderBy('position')
        ->pluck('points', 'position');
    }
}
