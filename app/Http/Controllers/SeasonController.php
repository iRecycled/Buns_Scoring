<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Intervention\Image\Size;
use PhpParser\Node\Expr\Cast\Bool_;

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
        try {
            $season->save();
            $scoring = new Scoring();
            $scoring->league_id = $request->leagueId;
            $scoring->season_id = $season->id;
            $scoring->qualifying = json_encode($request->qualifying_data);
            $scoring->heat = json_encode($request->heat_data);
            $scoring->consolation = json_encode($request->consolation_data);
            $scoring->feature = json_encode($request->feature_data);
            $scoring->fastest_lap = $request->fastest_lap;
            $dropWeeksEnabledInput = $request->enabled_drop_weeks;
            $scoring->enabled_drop_weeks = boolval($dropWeeksEnabledInput);
            $scoring->drop_weeks_start = $request->start_of_drop_score;
            $scoring->races_to_drop = $request->races_to_drop;
            $scoring->save();

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
            $scoring = new Scoring();
            $scoring->league_id = $leagueId;
            $scoring->season_id = $seasonId;
            $scoring->qualifying = json_encode($request->qualifying_data);
            $scoring->heat = json_encode($request->heat_data);
            $scoring->consolation = json_encode($request->consolation_data);
            $scoring->feature = json_encode($request->feature_data);
            $scoring->fastest_lap = (int) $request->fastest_lap;
            $scoring->enabled_drop_weeks = boolval($request->enabled_drop_weeks);
            $scoring->drop_weeks_start = (int) $request->start_of_drop_score;
            $scoring->races_to_drop = (int) $request->races_to_drop;
            $scoring->save();
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
            $scoringValues = [];
            for($i = 1; $i <= 60; $i++){
                $scoringValues[$i] = $jsonDecoded[strval($i)] ?? 0;
            }
            return $scoringValues;
        }

        $season = Season::where('id',$seasonId)->distinct()->get();
        $league = $season->first()->league;
        $qualifyingDb = Scoring::select('qualifying')->where('season_id',$seasonId)->value('qualifying');
        $heatDb = Scoring::select('heat')->where('season_id',$seasonId)->value('heat');
        $consolationDb = Scoring::select('consolation')->where('season_id',$seasonId)->value('consolation');
        $featureDb = Scoring::select('feature')->where('season_id',$seasonId)->value('feature');
        $qualifying = processType($qualifyingDb);
        $heat = processType($heatDb);
        $consolation = processType($consolationDb);
        $feature = processType($featureDb);

        $fastest_lap = Scoring::select('fastest_lap')->where('season_id',$seasonId)->value('fastest_lap');
        $enabled_drop_weeks = Scoring::select('enabled_drop_weeks')->where('season_id',$seasonId)->value('enabled_drop_weeks');
        $drop_week_enabled = $enabled_drop_weeks ? true : false;
        $drop_week_start = Scoring::select('drop_weeks_start')->where('season_id',$seasonId)->value('drop_weeks_start');
        $races_to_drop = Scoring::select('races_to_drop')->where('season_id',$seasonId)->value('races_to_drop');

        return view('season.editScoring', compact('league', 'season', 'qualifying', 'heat', 'consolation', 'feature', 'fastest_lap', 'drop_week_enabled', 'drop_week_start', 'races_to_drop'));
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
      ->select('display_name',
      DB::raw('SUM(race_points) as total_points'),
      DB::raw('SUM(laps_completed) as total_laps'),
      DB::raw('SUM(incidents) as total_incidents'),
      DB::raw('COUNT(*) as total_races'),
      DB::raw('SUM(laps_lead) as total_lead'),
      DB::raw('SUM(CASE WHEN finish_position = 1 THEN 1 ELSE 0 END) as total_wins'))
      ->where('season_id', $seasonId)
      ->where('simsession_name', '!=', 'QUALIFY')
      ->groupBy('display_name')
      ->get();

      $scoringQuery = Scoring::where('season_id', $seasonId)->get();
      $dropWeeksEnabled = boolval($scoringQuery[0]->enabled_drop_weeks);
      if($dropWeeksEnabled){
        $sessions = Session::select('display_name', 'race_points')
        ->where('season_id', $seasonId)
        ->where('simsession_name', '!=', 'QUALIFY')->get();
        $totalPointsByDriver = $this->applyDropWeeks($sessions, $scoringQuery);
        foreach($standings as $standing) {
            if (isset($totalPointsByDriver[$standing->display_name])) {
                $standing->total_points = $totalPointsByDriver[$standing->display_name];
            }
        }
      }
      $standings = $standings->sortByDesc('total_points')->values();
      $league = $seasons->first()->league;

      return view ('season.standings.standings', compact('seasonId', 'standings', 'league'));
    }

    private function applyDropWeeks($sessions, $scoringQuery){
        $startOfDropWeekScoring = $scoringQuery[0]->drop_weeks_start;
        $lowestRacesToDrop = $scoringQuery[0]->races_to_drop;

        $raceResultsByDriver = [];
        foreach ($sessions as $session) {
            $driverName = $session->display_name;
            $racePoints = $session->race_points;

            if (!isset($raceResultsByDriver[$driverName])) {
                $raceResultsByDriver[$driverName] = [];
            }
            $raceResultsByDriver[$driverName][] = $racePoints;
        }

        foreach ($raceResultsByDriver as $driverName => &$raceResults) {
            arsort($raceResults);
            if(count($raceResults) > $startOfDropWeekScoring) {
                $racesToDrop = $lowestRacesToDrop;
                $totalRaces = count($raceResults);
                if(($totalRaces - $lowestRacesToDrop) < $startOfDropWeekScoring){
                    $racesToDrop = abs($startOfDropWeekScoring - $totalRaces);
                }
                // $pointsRemovedByDrops = array_sum(array_slice($raceResults, -$racesToDrop)); //helpful to check if this is working
                $raceResults = array_slice($raceResults, 0, -$racesToDrop);
            }
        }
        $finalTotalPointsByDriver = [];
        foreach ($raceResultsByDriver as $driverName => $raceResults) {
            $finalTotalPointsByDriver[$driverName] = array_sum($raceResults);
        }
        return $finalTotalPointsByDriver;
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
                      'subsession_id' => $sessionId,
                      'race_date' => Carbon::parse($data->start_time)->format('Y-m-d')
                    ], $record);
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
            if($race->interval == '-' && $race->finish_position != 1){
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
        $qualy_points = json_decode($scoringQuery[0]->qualifying, true);
        $heat_points = json_decode($scoringQuery[0]->heat, true);
        $consolation_points = json_decode($scoringQuery[0]->consolation, true);
        $feature_points = json_decode($scoringQuery[0]->feature, true);
        $fastest_lap_points = $scoringQuery[0]->fastest_lap;

        $fastestDrivers = [];
        $lowestFastestLapTime = null;
        $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        $validSessionPatternWithoutQualy = '/^(CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        foreach($results as $racer) {
            if(preg_match($validSessionPattern, $racer->simsession_name)){
                if (preg_match($validSessionPatternWithoutQualy, $racer->simsession_name)) {
                    if($racer->best_lap_time != '-'){
                        if(strpos($racer->best_lap_time, ':') != false) {
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
                    }
                }
                switch ($racer->simsession_name) {
                    case 'QUALIFY':
                        $racer->race_points = $qualy_points[$racer->finish_position];
                        break;
                    case 'CONSOLATION':
                        $racer->race_points = $consolation_points[$racer->finish_position];
                        break;
                    case 'RACE':
                    case 'FEATURE':
                        $racer->race_points = $feature_points[$racer->finish_position];
                        break;
                    default:
                        if(strpos($racer->simsession_name, 'HEAT') != false){
                            $racer->race_points = $heat_points[$racer->finish_position];
                        }
                        break;
                  }
                  Session::where('id', $racer->id)->update(['race_points' => $racer->race_points]);
            }
        }
        $driverIdsFastest = array_column($fastestDrivers, 'id');

        Session::whereIn('id', $driverIdsFastest)
            ->update([
                'race_points' => DB::raw('race_points + ' . $fastest_lap_points)
            ]);
    }

    public function deleteSession(Request $req) {
        $sessions = Session::where('subsession_id', $req->sessionId)->get();
        $leagueId = $sessions[0]->league_id;
        $league = League::where('leagueId', $leagueId)->first();
        if($league->league_owner_id == $req->userId) {
            $sessions->each->delete();
        } else {
            return redirect()->back()->withErrors(['message' => 'Season failed to delete']);
        }
        return redirect("/season/". $req->seasonId)->with('success', 'Season deleted successfully');
    }
}
