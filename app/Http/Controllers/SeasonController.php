<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use Illuminate\Support\Facades\DB;
use Exception;

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

        $inputFields = [
            'qualifying_data' => 'qualifying',
            'heat_data' => 'heat',
            'consolation_data' => 'consolation',
            'feature_data' => 'feature',
        ];
        try {
            $season->save();
            foreach ($inputFields as $inputField => $propertyName) {
                $jsonData = json_encode($request->input($inputField));

                $scoring = new Scoring();
                $scoring->league_id = $request->leagueId;
                $scoring->scoring_json = $jsonData;
                $scoring->season_id = $season->id;
                $scoring->race_type = $propertyName;
                $scoring->save();
            }

            return redirect()->route('league.showLeague', ['leagueId' => $season->league_id])->with('success', 'Season created successfully');
            }
        catch(Exception $e){
            return redirect()->back()->withErrors(['message' => 'Season failed to create']);
        }
    }

    public function create_season($leagueId){
        $league = League::where('leagueId', $leagueId)->get();
        $count = Season::where('league_id', $leagueId)->count();
        return view('league.create_season', compact('league', 'count'));
    }

    public function updateScoring(Request $request, $seasonId){
        try {
            DB::beginTransaction();
            $leagueId = Scoring::where('season_id', $seasonId)->value('league_id');
            Scoring::where('season_id', $seasonId)->delete();
            $inputFields = [
                'qualifying_data' => 'qualifying',
                'heat_data' => 'heat',
                'consolation_data' => 'consolation',
                'feature_data' => 'feature',
            ];
                foreach ($inputFields as $inputField => $propertyName) {
                    $jsonData = json_encode($request->input($inputField));
                    $scoring = new Scoring();
                    $scoring->league_id = $leagueId;
                    $scoring->scoring_json = $jsonData;
                    $scoring->season_id = $seasonId;
                    $scoring->race_type = $propertyName;
                    $scoring->save();
                }
            DB::commit();
            return redirect("/season/". $seasonId)->with('success', 'Scoring updated successfully');
        }
        catch(Exception $e){
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }

    }

    public function editScoring($seasonId){
        function processType($race_json){
            $jsonDecoded = json_decode($race_json, true);
            $decodedArray = [];
            foreach ($jsonDecoded as $jsonString) {
                $decodedArray[] = json_decode($jsonString, true);
            }
            $scoringValues = [];
            for($i = 1; $i <= 60; $i++){
                $scoringValues[$i] = $decodedArray[0][$i] ?? 0;
            }
            return $scoringValues;
        }

        $season = Season::where('id',$seasonId)->distinct()->get();
        $league = $season->first()->league;
        $qualifyingDb = Scoring::where('season_id',$seasonId)->where('race_type','qualifying')->pluck('scoring_json');
        $qualifying = processType($qualifyingDb);
        $heatDb = Scoring::where('season_id',$seasonId)->where('race_type','heat')->pluck('scoring_json');
        $heat = processType($heatDb);
        $consolationDb = Scoring::where('season_id',$seasonId)->where('race_type','consolation')->pluck('scoring_json');
        $consolation = processType($consolationDb);
        $featureDb = Scoring::where('season_id',$seasonId)->where('race_type','feature')->pluck('scoring_json');
        $feature = processType($featureDb);
        return view('season.editScoring', compact('league', 'season', 'qualifying', 'heat', 'consolation', 'feature'));
    }

    public function showSeason($seasonId){
        $seasons = Season::where('id',1)->distinct()->get();
        $seasons = Season::where('id',$seasonId)->distinct()->get();
        $league = $seasons->first()->league;
        $leagues_sessions = Session::where('league_id', $league->leagueId)->where('season_id',$seasonId)->get();
        $unique_leagues_sessions = $leagues_sessions->unique('subsession_id');
        return view('season.season', compact(
            'seasonId',
            'unique_leagues_sessions',
            'league'
        ));
    }

    public function showStandings($seasonId){
      $seasons = Season::where('id',$seasonId)->distinct()->get();
      $league = $seasons->first()->league;
      $standings = DB::table('sessions')
      ->select('display_name', DB::raw('SUM(race_points) as total_points'))
      ->where('season_id', $seasonId)
      ->groupBy('display_name')
      ->orderByDesc('total_points')
      ->get();
      return view ('season.standings.standings', compact('seasonId', 'standings', 'league'));
    }

    public function newSessionSubmit(Request $request, $leagueId, $seasonId){
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
          $results = $data->session_results;
          $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
          foreach($results as $result){
            if(preg_match($validSessionPattern, $result->simsession_name)){
                foreach($result->results as $realResults){
                    $record = [
                        'subsession_id' => $sessionId,
                        'simsession_name' => $result->simsession_name,
                        'finish_position' => ++$realResults->finish_position,
                        'race_points' => 0,
                        'display_name' => $realResults->display_name,
                        'league_id' => $leagueId,
                        'season_id' => $seasonId
                    ];
                  DB::table('sessions')->updateOrInsert([
                      'simsession_name' => $result->simsession_name,
                      'finish_position' => $realResults->finish_position,
                      'subsession_id' => $sessionId], $record);
                  }
                }
          }
          $this->updateRacePoints($seasonId);
          $url = url('session/'. $sessionId);
          return redirect($url)->with(compact('leagueId'));
      }

      private function updateRacePoints($seasonId){
        $sessions = Session::where('season_id', $seasonId)->get();
        $scoringQuery = Scoring::where('season_id', $seasonId)->get();
        $qualy_json = json_decode($scoringQuery[0]->scoring_json, true);
        $heat_json = json_decode($scoringQuery[1]->scoring_json, true);
        $consolation_json = json_decode($scoringQuery[2]->scoring_json, true);
        $feature_json = json_decode($scoringQuery[3]->scoring_json, true);
        $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        foreach($sessions as $racer){
            if(preg_match($validSessionPattern, $racer->simsession_name)){
                switch ($racer->simsession_name) {
                    case 'QUALIFY':
                        $racer->race_points = $qualy_json[$racer->finish_position];
                        break;
                    case 'CONSOLATION':
                        $racer->race_points = $consolation_json[$racer->finish_position];
                        break;
                    case 'RACE':
                    case 'FEATURE':
                        $racer->race_points = $feature_json[$racer->finish_position];
                        break;
                    default:
                        if(strpos($racer->simsession_name, 'HEAT') !== false){
                            $racer->race_points = $heat_json[$racer->finish_position];
                        }
                        break;
                  }
                  Session::where('id', $racer->id)->update(['race_points' => $racer->race_points]);
            }
        }
    }
}
