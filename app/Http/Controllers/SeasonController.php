<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use Illuminate\Support\Facades\DB;

class SeasonController extends Controller
{
    public function createSeason(Request $request)
    {
        $request->validate([
            'season_name' => 'required',
        ]);
        $season = new Season();
        $season->season_name = $request->season_name;
        $season->league_id = $request->leagueId;
        $season->season_count = $request->season_count;

        $scoringInput = $request->input('scoring_column');
        $json = json_encode($scoringInput);
        $score = new Scoring();
        $score->league_id = $request->leagueId;
        $score->scoring_json = $json;

        try {
          $season->save();
          $score->season_id = $season->id;
          $score->save();
          return redirect()->route('league.showLeague', ['leagueId' => $season->league_id])->with('success', 'Season created successfully');
        } catch(Exception $e){
          return redirect()->back()->withErrors(['message' => 'Season failed to create']);
        }        
    }

    public function create_season($leagueId){
        $league = League::where('leagueId', $leagueId)->get();
        $count = Season::where('league_id', $leagueId)->count();
        return view('league.create_season', compact('league', 'count'));
    }

    public function showSeason($seasonId){
        $seasons = Season::where('id',$seasonId)->distinct()->get();
        $league = $seasons->first()->league;
        $leagues_sessions = Session::where('league_id', $league->leagueId)->where('season_id',$seasonId)->get();
        $unique_leagues_sessions = $leagues_sessions->unique('subsession_id');
        return view('season.season', compact(
            'seasonId',
            'unique_leagues_sessions',
            'league',
        ));
    }

    public function showStandings($seasonId){
      $standings = DB::table('sessions')
      ->select('display_name', DB::raw('SUM(race_points) as total_points'))
      ->where('season_id', $seasonId)
      ->groupBy('display_name')
      ->get();
      return view ('season.standings.standings', compact('seasonId', 'standings'));
    }
    
}
