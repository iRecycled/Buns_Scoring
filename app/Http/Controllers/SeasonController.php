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
            'qualifying_data' => 'QUALIFY',
            'heat_data' => 'HEAT',
            'consolation_data' => 'CONSOLATION',
            'feature_data' => 'FEATURE',
            'fastest_lap' => 'FASTEST',
            'pole_position' => 'POLE'
        ];
        try {
            $season->save();
            foreach ($inputFields as $key => $propertyName) {
                $jsonData = json_encode($request->input($key));

                $scoring = new Scoring();
                $scoring->league_id = $request->leagueId;
                $scoring->scoring_json = $jsonData;
                $scoring->season_id = $season->id;
                $scoring->race_type = $key;
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
                'qualifying_data' => 'QUALIFY',
                'heat_data' => 'HEAT',
                'consolation_data' => 'CONSOLATION',
                'feature_data' => 'FEATURE',
                'fastest_lap' => 'FASTEST',
                'pole_position' => 'POLE'
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
            $this->updateRacePoints($seasonId);
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
        $qualifyingDb = Scoring::where('season_id',$seasonId)->where('race_type','QUALIFY')->pluck('scoring_json');
        $qualifying = processType($qualifyingDb);
        $heatDb = Scoring::where('season_id',$seasonId)->where('race_type','HEAT')->pluck('scoring_json');
        $heat = processType($heatDb);
        $consolationDb = Scoring::where('season_id',$seasonId)->where('race_type','CONSOLATION')->pluck('scoring_json');
        $consolation = processType($consolationDb);
        $featureDb = Scoring::where('season_id',$seasonId)->where('race_type','FEATURE')->pluck('scoring_json');
        $feature = processType($featureDb);
        $poleDB = Scoring::where('season_id',$seasonId)->where('race_type','POLE')->pluck('scoring_json');
        $pole = isset($poleDB[0]) ? floatval(str_replace(',', '', preg_replace('/[^0-9,.]/', '', $poleDB[0]))) : 0;
        $fastest_lapDB = Scoring::where('season_id',$seasonId)->where('race_type','FASTEST')->pluck('scoring_json');
        $fastest_lap = isset($fastest_lapDB[0]) ? floatval(str_replace(',', '', preg_replace('/[^0-9,.]/', '', $fastest_lapDB[0]))) : 0;
        return view('season.editScoring', compact('league', 'season', 'qualifying', 'heat', 'consolation', 'feature','pole', 'fastest_lap'));
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

    public function newSessionSubmit(Request $request, $leagueId, $seasonId) {
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
                        'season_id' => $seasonId,
                        'laps_lead' => $realResults->laps_lead,
                        'laps_completed' => $realResults->laps_complete,
                        'average_lap_time' => $this->convertTime($realResults->average_lap),
                        'best_lap_time' => $this->convertTime($realResults->best_lap_time),
                        'best_lap_number' => $realResults->best_lap_num,
                        'qualifying_lap_time' => $this->convertTime($realResults->best_qual_lap_time),
                        'starting_pos' => ++$realResults->starting_position,
                        'interval' => $this->convertTime($realResults->interval),
                        'incidents' => $realResults->incidents,
                        'club_name' => $realResults->club_name
                    ];
                  DB::table('sessions')->updateOrInsert([
                      'simsession_name' => $result->simsession_name,
                      'finish_position' => $realResults->finish_position,
                      'license_category' => $data->license_category,
                      'corners_per_lap' => $data->corners_per_lap,
                      'track_name' => $data->track->track_name,
                      'config_name' => $data->track->config_name,
                      'temp_value' => $data->weather->temp_value,
                      'temp_units' => $data->weather->temp_units,
                      'rel_humidity' => $data->weather->rel_humidity,
                      'subsession_id' => $sessionId], $record);
                  }
                }
          }
          $this->setIntervalByLeader($sessionId);
          $this->updateRacePoints($seasonId);
          $url = url('session/'. $sessionId);
          return redirect($url)->with(compact('leagueId'));
      }

      private function convertTime($time){
        if($time <= 0) return "-";
        $minutes = floor((int) substr($time, 0, -4) / 60);
        $seconds = (int) substr( $time, 0 , -4) % 60;
        $milliseconds = (int) substr($time, -4, 3);
        if($minutes == 0){
            return sprintf('%02d.%03d', $seconds, $milliseconds);
        }
        return sprintf('%d:%02d.%03d', $minutes, $seconds, $milliseconds);
      }

      private function calcInterval($simsession_name, $subsession_id, $leadersLapsComplete){
      $sessions = Session::where('simsession_name', $simsession_name)->where('subsession_id', $subsession_id)->get(['id','finish_position', 'interval', 'laps_completed']);
        foreach ($sessions as $race) {
            if($race->interval == '-' && $race->finish_position !== 1){
                $lapsBehind = $leadersLapsComplete - $race->laps_completed;
                $race->interval = "-" . abs($lapsBehind) . " laps";
            }

            Session::where('id', $race->id)->update(['interval' => $race->interval]);
        }
      }

      private function setIntervalByLeader($subsession_id){
        $leaders = Session::where('subsession_id', $subsession_id)->where('finish_position', 1)->get(['laps_completed', 'interval', 'simsession_name']);
        //at most loops 5 times.
        foreach ($leaders as $leader) {
            $this->calcInterval($leader->simsession_name, $subsession_id, $leader->laps_completed);
        }
    }

    private function updateRacePoints($seasonId){
        $results = Session::where('season_id', $seasonId)->get();
        $scoringQuery = Scoring::where('season_id', $seasonId)->get();
        $qualy_json = json_decode($scoringQuery[0]->scoring_json, true);
        $heat_json = json_decode($scoringQuery[1]->scoring_json, true);
        $consolation_json = json_decode($scoringQuery[2]->scoring_json, true);
        $feature_json = json_decode($scoringQuery[3]->scoring_json, true);
        $fastest_lap_points = str_replace('"', '', $scoringQuery[4]->scoring_json);
        $pole_points = str_replace('"', '', $scoringQuery[5]->scoring_json);
        $fastestDrivers = [];
        $lowestFastestLapTime = null;
        $polePositionDrivers = [];
        $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        $validSessionPatternWithoutQualy = '/^(CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        foreach($results as $racer){
            if(preg_match($validSessionPattern, $racer->simsession_name)){
                if (preg_match($validSessionPatternWithoutQualy, $racer->simsession_name)) {
                    if($racer->best_lap_time !== '-'){
                        if(strpos($racer->best_lap_time, ':' !== false)) {
                            list($minutes, $seconds) = explode(':', $racer->best_lap_time);
                        } else {
                            $minutes = 0;
                            $seconds = $racer->best_lap_time;
                        }
                        list($wholeSeconds, $milliseconds) = explode('.', $seconds);
                        $totalSeconds = $minutes * 60 + $wholeSeconds + ($milliseconds / 1000);
                        $sessionType = $racer->simsession_name;
                        if (!isset($lowestFastestLapTime[$sessionType]) || $totalSeconds < $lowestFastestLapTime[$sessionType]) {
                            $lowestFastestLapTime[$sessionType] = $totalSeconds;
                            $fastestDrivers[$sessionType] = $racer;
                        }
                        if ($racer->finish_position == 1){
                            $polePositionDrivers[$sessionType] = $racer;
                        }
                    }

                }
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
        $driverIdsPole = array_column($polePositionDrivers, 'id');
        $driverIdsFastest = array_column($fastestDrivers, 'id');
        Session::whereIn('id', $driverIdsPole)
        ->update([
            'race_points' => DB::raw('race_points + ' . $pole_points)
        ]);

        Session::whereIn('id', $driverIdsFastest)
            ->update([
                'race_points' => DB::raw('race_points + ' . $fastest_lap_points)
            ]);
    }
}
