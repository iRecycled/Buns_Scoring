<?php

namespace App\Http\Controllers;
use App\Models\Session;
use App\Models\Scoring;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function showSession($sessionId){
        $sessions = Session::where('subsession_id',$sessionId)->get();
        $season_id = $sessions->first()->season_id;
        $league = $sessions->first()->league;
        $unique_types = $sessions->unique('simsession_name');
        $drivers = $sessions->pluck('display_name')->unique();
        $currentData = Session::select('penalty_seconds','penalty_points','display_name','simsession_name')
        ->whereNotNull('penalty_seconds')
        ->orWhereNotNull('penalty_points')
        ->where('subsession_id',$sessionId)->get();
        foreach($unique_types as $type) {
            $types[] = $type->simsession_name;
        }
        $calculatedResults = $this->updateIntervalByPenalties($sessionId, $types);
        $calculatedResults = $this->convertIntervalBackToMinutes($calculatedResults);
        $this->updateRacePoints($season_id, $calculatedResults);
        return view('session.session', compact(
            'sessions',
            'sessionId',
            'types',
            'league',
            'season_id',
            'drivers',
            'currentData',
            'calculatedResults'
        ));
    }

    public function submitPenalties(Request $request, $sessionId){
        $currentData = Session::where('subsession_id', $sessionId)->get();
        foreach ($currentData as $key => $value) {
            $currentData[$key]->penalty_points = null;
            $currentData[$key]->penalty_seconds = null;
            $currentData[$key]->save();
        }
        $data = $request->all();

        if(array_key_exists('driver',$data)){
            foreach ($data['driver'] as $key => $driverName){
                $penaltyPoints = $data['penaltyPoints'][$key];
                $penaltyTime = $data['penaltyTime'][$key];
                $penaltySession = $data['penalty-session'];

                $session = Session::where('display_name',$driverName)
                ->where('subsession_id', $sessionId)
                ->where('simsession_name', $penaltySession)
                ->first();

                if($session){
                    $session->penalty_points = $penaltyPoints;
                    $session->penalty_seconds = $penaltyTime;
                    $session->save();
                }
            }
        }
        return $this->showSession($sessionId);
    }

    private function updateIntervalByPenalties($subsession_id, $session_types){
        $mergedResultsForAllSessions = new Collection();
        foreach ($session_types as $key => $session) {
            $leadLappers = Session::where('simsession_name', $session)->where('subsession_id',$subsession_id)->where('interval','NOT LIKE','%laps')->get();
            $lappedDrivers = Session::where('simsession_name', $session)->where('subsession_id',$subsession_id)->where('interval','LIKE','%laps')->get();

            $index = 1;
            $mergedResults = $leadLappers->sortBy('actualInterval')->concat($lappedDrivers);
            foreach ($mergedResults as $key => $result) {
                $mergedResults[$key]->finish_position = $index;
                $index++;
            }
            $mergedResultsForAllSessions = $mergedResultsForAllSessions->concat($mergedResults);
        }
        return $mergedResultsForAllSessions;
    }

    private function convertIntervalBackToMinutes($results){
        $tempResults = [];
        foreach ($results as $key => $result) {
            if ($result->actualInterval > 60) {
                $minutes = floor($result->actualInterval / 60);
                $remainingSeconds = $result->actualInterval % 60;
                $milliseconds = substr(sprintf('%0.3f', $result->actualInterval - floor($result->actualInterval)), 2);
                $result->interval = "{$minutes}:" . str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . ".{$milliseconds}";
            } else if(strpos($result->actualInterval, "laps") === false ){
                $remainingSeconds = $result->actualInterval % 60;
                $milliseconds = substr(sprintf('%0.3f', $result->actualInterval - floor($result->actualInterval)), 2);
                $result->interval = str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . ".{$milliseconds}";
            }
            $tempResults[] = $result;
        }
        return $tempResults;
    }

    private function updateRacePoints($seasonId, $results){
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
                        list($minutes, $seconds) = explode(':', $racer->best_lap_time);
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
        //for local db
        foreach($polePositionDrivers as $driver){
            $driver->race_points += $pole_points;
        }
        foreach($fastestDrivers as $driver){
            $driver->race_points += $fastest_lap_points;
        }
        $driverIdsPole = array_column($polePositionDrivers, 'id');
        $driverIdsFastest = array_column($fastestDrivers, 'id');

        //updating the score on the real table
        Session::whereIn('id', $driverIdsPole)
        ->update([
            'race_points' => DB::raw('race_points + ' . $pole_points)
        ]);

        Session::whereIn('id', $driverIdsFastest)
            ->update([
                'race_points' => DB::raw('race_points + ' . $fastest_lap_points)
            ]);
    }

    public function getSeason($subsession_id){
        $season_id = Session::select('season_id')->where('subsession_id', $subsession_id)->first();
        return redirect()->route('season.showSeason', ['id', $season_id]);
    }
}
