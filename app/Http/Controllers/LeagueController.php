<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
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

    public function showSession($sessionId){
      $sessions = Session::where('subsession_id',$sessionId)->distinct()->get();
      $league = $sessions->first()->league;
      $unique_types = $sessions->unique('simsession_name');
      foreach($unique_types as $type) {
          $types[] = $type->simsession_name;
      }
      return view('session.session', compact(
          'sessions',
          'sessionId',
          'types',
          'league',
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
        $league_season_name = $data->league_season_name;// ?? NULL;
        $results = $data->session_results;
        $records = [];
        foreach($results as $result){
            //sim session is a race (6)
            if($result->simsession_type === 6){
                foreach($result->results as $result2){
                    $records[] = [
                        'subsession_id' => $sessionId,
                        'simsession_name' => $result->simsession_name,
                        'league_season_name' => $league_season_name,
                        'finish_position' => ++$result2->finish_position,
                        'display_name' => $result2->display_name,
                        'league_id' => $leagueId,
                        'season_id' => $seasonId
                    ];
                }
            }
        }
        DB::table('sessions')->insert($records);
        return redirect()->route('session.showSession', ['sessionId' => $sessionId])
        ->with(compact('leagueId'));
    }
}
