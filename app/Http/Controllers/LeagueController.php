<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use Exception;
// use iRacingPHP\iRacing;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ErrorHandler\Debug;

use function PHPUnit\Framework\isEmpty;

class LeagueController extends Controller
{
    public function createLeague(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:leagues',
            'description',
            'leagueId' => 'required|unique:leagues',
        ]);

        $league = new League();
        $league->name = $request->name;
        $league->description = "";
        if($request->description){
          $league->description = $request->description;
        }
        $league->leagueId = $request->leagueId;

        try {
            //TODO figure out how to test auth without cookies
            if($league->save())
            {
              return redirect()->route('league.showLeague', ['leagueId' => $league->leagueId])->with('success', 'League created successfully');
            }
            else {
              return redirect()->route('create')->withErrors(['message' => 'League failed to create']);
            }
          } catch (Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
          }
    }

    public function showLeague($leagueId){
        $seasons = Season::where('league_id',$leagueId)->distinct()->get();
        return view('league.home', compact(
            'leagueId',
            'seasons',
        ));
    }

    public function leagueSessionSubmit(Request $request, $leagueId, $seasonId){
      info($request->file('json_file'));
      if($request->file('json_file')){
        $file = $request->file('json_file');
        $json = $file->getContent();
        $data = json_decode($json);
      }
      else {
        return redirect()->back()->withErrors(['message' => 'Failed to load file']);
      }

        $sessionId = $data->subsession_id;
        $leagueIdInJson = $data->league_id;// ?? 'null';
        $results = $data->session_results;
        $scoringQuery = Scoring::where('league_id', $leagueId)
               ->where('season_id', $seasonId)
               ->first();
        $seasonJsonData = json_decode($scoringQuery->scoring_json, true);
        foreach($results as $result){
            //sim session is a race (6)
            if($result->simsession_type === 6){
                foreach($result->results as $result2){
                    $record = [
                        'subsession_id' => $sessionId,
                        'simsession_name' => $result->simsession_name,
                        'finish_position' => ++$result2->finish_position,
                        'race_points' => $seasonJsonData[$result2->finish_position],
                        'display_name' => $result2->display_name,
                        'league_id' => $leagueId,
                        'season_id' => $seasonId
                    ];
                DB::table('sessions')->updateOrInsert([
                    'simsession_name' => $result->simsession_name,
                    'finish_position' => $result2->finish_position,
                    'subsession_id' => $sessionId], $record);
                }
            }
        }
        $url = url('session/'. $sessionId);
        return redirect($url)->with(compact('leagueId'));
    }
}
