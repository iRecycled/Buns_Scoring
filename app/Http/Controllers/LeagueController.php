<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use Exception;
// use iRacingPHP\iRacing;
use Illuminate\Support\Facades\DB;


use function PHPUnit\Framework\isEmpty;

class LeagueController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:leagues',
            'description',
            'leagueId' => 'required|unique:leagues',
        ]);

        $league = new League();
        $league->name = $request->name;
        if($request->description){
          $league->description = $request->description;
        }
        $league->leagueId = $request->leagueId;
        // $league->email = $request->email;
        // $concat = mb_convert_encoding($request->password . strtolower($request->email), 'UTF-8');
        // $hash = hash('sha256', $concat, true);
        // $league->password = base64_encode($hash);

        try {
            //TODO figure out how to test auth without cookies
            if($league->save())
            {
              return redirect()->route('create')->with('success', 'League created successfully');
            }
            else {
              return redirect()->route('create')->withErrors(['message' => 'League failed to create']);
            }
          } catch (Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
          }
    }

    public function show($leagueId, $sessionId = null){
      if($sessionId){
        return view('league.session', compact('leagueId', 'sessionId'));
      } else {
        return view('league.home', compact('leagueId'));
      }
    }

    public function leagueSessionSubmit(Request $request, $leagueId){
        $file = $request->file('json_file');
        $json = $file->getContent();
        $data = json_decode($json);

        $sessionId = $data->subsession_id;
        $leagueIdInJson = $data->league_id;
        $league_season_name = $data->league_season_name;
        $results = $data->session_results;
        $records = [];
        foreach($results as $result){
            if($result->simsession_type_name == "Race" && $result->simsession_number == 0){
                foreach($result->results as $result2){
                    $records[] = [
                        'subsession_id' => $sessionId,
                        'league_season_name' => $league_season_name,
                        'finish_position' => ++$result2->finish_position,
                        'display_name' => $result2->display_name,
                        'league_id' => $leagueIdInJson
                    ];
                }
            }
        }
        DB::table('sessions')->insert($records);
        return view('league.home', compact('leagueId'));
    }
}
