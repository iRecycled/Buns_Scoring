<?php

namespace App\Http\Controllers;
use App\Models\Session;
use App\Models\Scoring;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SessionController extends Controller
{
    public function showSession($sessionId){
        $sessions = Session::where('subsession_id',$sessionId)->get();
        $season_id = $sessions->first()->season_id;
        $league = $sessions->first()->league;
        $unique_types = $sessions->unique('simsession_name');
        $drivers = $sessions->pluck('display_name')->unique();
        $currentData = $sessions->filter(function ($session) {
            return $session->penalty_seconds != null || $session->penalty_points != null;
        });
        foreach($unique_types as $type) {
            $types[] = $type->simsession_name;
        }
        //key value pair for session_name and lead drivers laps completed
        foreach($types as $session_name) {
            $sessionTypesWithRaceLaps[$session_name] = $sessions->filter(function ($driver) use ($session_name) {
                return $driver->simsession_name == $session_name && $driver->finish_position == 1;
            })->first()->laps_completed;
        }
        $calculatedResults = $this->updateIntervalByPenalties($sessions, $types);
        $calculatedResults = $this->convertIntervalBackToMinutes($calculatedResults);
        $this->updateRacePoints($season_id, $calculatedResults, $sessionTypesWithRaceLaps);
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
                $penaltySession = $data['penalty-session'][$key];
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
        return redirect("/session/{$sessionId}");
    }

    private function updateIntervalByPenalties($sessionData, $session_types){
        $mergedResultsForAllSessions = new Collection();
        $leadLappers = $sessionData->filter(function ($session) {
            return strpos($session['interval'], 'laps') == false;
        });
        $lappedDrivers = $sessionData->filter(function ($session) {
            return strpos($session['interval'], 'laps') == true;
        });
        foreach ($session_types as $key => $session) {
            $sessionLappers = $leadLappers->filter(function ($item) use ($session) {
                return $item->simsession_name == $session;
            });
            $sessionDrivers = $lappedDrivers->filter(function ($item) use ($session) {
                return $item->simsession_name == $session;
            });
            $index = 1;
            $mergedResults = $sessionLappers->sortBy('actualInterval')->concat($sessionDrivers);
            foreach ($mergedResults as $key => $result) {
                $mergedResults[$key]->finish_position = $index;
                $index++;
            }
            $mergedResultsForAllSessions = $mergedResultsForAllSessions->concat($mergedResults);
        }
        $mergedResultsForAllSessions->each->save();
        return $mergedResultsForAllSessions;
    }

    private function convertIntervalBackToMinutes($results){
        $tempResults = new Collection();
        foreach ($results as $key => $result) {
            if ($result->actualInterval > 60) {
                $minutes = floor($result->actualInterval / 60);
                $remainingSeconds = $result->actualInterval % 60;
                $milliseconds = substr(sprintf('%0.3f', $result->actualInterval - floor($result->actualInterval)), 2);
                $result->interval = "{$minutes}:" . str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . ".{$milliseconds}";
            } else if(strpos($result->actualInterval, "laps") == false ){
                $remainingSeconds = $result->actualInterval % 60;
                $milliseconds = substr(sprintf('%0.3f', $result->actualInterval - floor($result->actualInterval)), 2);
                $result->interval = str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . ".{$milliseconds}";
            }
            $tempResults[$key] = $result;
        }
        return $tempResults;
    }

    private function updateRacePoints($seasonId, $results, $sessionTypesWithRaceLaps){
        $scoringQuery = Scoring::where('season_id', $seasonId)->get();
        if($scoringQuery) {
            $qualy_points = json_decode($scoringQuery[0]->qualifying, true);
            $heat_points = json_decode($scoringQuery[0]->heat, true);
            $consolation_points = json_decode($scoringQuery[0]->consolation, true);
            $feature_points = json_decode($scoringQuery[0]->feature, true);

            $percentLapsEnabled = $scoringQuery[0]->enabled_percentage_laps;
            $percentLapsValue = $scoringQuery[0]->lap_percentage_to_complete;

            $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
            foreach($results as $key => $racer){
                if(preg_match($validSessionPattern, $racer->simsession_name)) {
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
                        if(str_contains($racer->simsession_name, "HEAT")){
                                $racer->race_points = $heat_points[$racer->finish_position];
                            }
                            break;
                      }
                }

                $minLaps = (int) $sessionTypesWithRaceLaps[$racer->simsession_name] * ($percentLapsValue / 100);
                if($percentLapsEnabled && $racer->laps_completed < $minLaps && $racer->simsession_name != "QUALIFY") {
                    $racer->race_points = 0;
                }
                if($racer->fastest_lap_points > 0 && $racer->simsession_name != "QUALIFY"){
                    $racer->race_points += $racer->fastest_lap_points;
                }
            }
        }
    }

    public function getSeason($subsession_id){
        $season_id = Session::select('season_id')->where('subsession_id', $subsession_id)->first();
        return redirect()->route('season.showSeason', ['id', $season_id]);
    }
}
