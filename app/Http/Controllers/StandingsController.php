<?php

namespace App\Http\Controllers;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

class StandingsController extends Controller
{
    public function showStandings($seasonId){
        $seasons = Season::where('id',$seasonId)->distinct()->get();
        $league = $seasons->first()->league;
        $seasonSessions = Session::where('league_id', $league->leagueId)->where('season_id', $seasonId)->get();
        $seasonSubsessionIds = $seasonSessions->pluck('subsession_id')->unique();
        $standings = collect();
        foreach($seasonSubsessionIds as $subsession_id) {
            $session = $seasonSessions->filter(function ($sesh) use ($subsession_id) {
                return $sesh->subsession_id == $subsession_id;
            });
            $tempStanding = $this->applySessionScoring($session, $seasonId);
            $standings = $standings->concat($tempStanding);
        }

        // $standings = DB::table('sessions')
        // ->select('display_name',
        // DB::raw('SUM(race_points) as total_points'),
        // DB::raw('SUM(laps_completed) as total_laps'),
        // DB::raw('SUM(incidents) as total_incidents'),
        // DB::raw('COUNT(*) as total_races'),
        // DB::raw('SUM(laps_lead) as total_lead'),
        // DB::raw('SUM(CASE WHEN finish_position = 1 THEN 1 ELSE 0 END) as total_wins'))
        // ->where('season_id', $seasonId)
        // ->where('simsession_name', '!=', 'QUALIFY')
        // ->groupBy('display_name')
        // ->get();

        $scoringQuery = Scoring::where('season_id', $seasonId)->get();
        $dropWeeksEnabled = boolval($scoringQuery[0]->enabled_drop_weeks);
        if($dropWeeksEnabled){
            $sessions = $standings->filter(function ($sesh) {
                return !str_contains($sesh->simsession_name, "QUALIFY");
            });
            // $sessions = Session::select('display_name', 'race_points')
            // ->where('season_id', $seasonId)
            // ->where('simsession_name', '!=', 'QUALIFY')->get();
            $totalPointsByDriver = $this->applyDropWeeks($sessions, $scoringQuery);
            foreach($standings as $standing) {
                if (isset($totalPointsByDriver[$standing->display_name])) {
                    $standing->total_points = $totalPointsByDriver[$standing->display_name];
                }
            }
        }

        $grouped = $standings->groupBy('display_name');
        $standings = $grouped->map(function ($sessions, $displayName) use ($scoringQuery, $dropWeeksEnabled) {
            $totalRaces = $sessions->filter(function ($session) {
                return !str_contains($session->simsession_name, "QUALIFY");
            })->count();
            $startOfDropWeekScoring = $scoringQuery[0]->drop_weeks_start;
            $lowestRacesToDrop = $scoringQuery[0]->races_to_drop;
            $racesToDrop = 0;
            if($dropWeeksEnabled && $totalRaces > $startOfDropWeekScoring){
                if((($totalRaces - $lowestRacesToDrop) <= $startOfDropWeekScoring)){
                    $racesToDrop = abs($startOfDropWeekScoring - $totalRaces);
                } else {
                    $racesToDrop = $lowestRacesToDrop;
                }
            }

            return [
                'display_name' => $displayName,
                'total_points' => isset($sessions->first()->total_points) ? $sessions->first()->total_points : $sessions->sum('race_points'),
                'total_laps' => $sessions->sum('laps_completed'),
                'total_incidents' => $sessions->sum('incidents'),
                'total_races' => $totalRaces,
                'total_lead' => $sessions->sum('laps_lead'),
                'total_wins' => $sessions->where('finish_position', 1)->count(),
                'races_dropped' => $racesToDrop
            ];
        });
        $standings = $standings->sortByDesc('total_points');
        // $standings = $standings->sortByDesc('total_points')->values();
        $league = $seasons->first()->league;

        return view ('season.standings.standings', compact('seasonId', 'standings', 'league'));
    }

    private function applyDropWeeks($sessions, $scoringQuery){
        $startOfDropWeekScoring = $scoringQuery[0]->drop_weeks_start;
        $lowestRacesToDrop = $scoringQuery[0]->races_to_drop;
        $raceResultsByDriver = [];
        foreach ($sessions as $session) {
            $driverName = $session['display_name'];
            $racePoints = (int)$session['race_points'];

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
                if(($totalRaces - $lowestRacesToDrop) <= $startOfDropWeekScoring){
                    $racesToDrop = abs($startOfDropWeekScoring - $totalRaces);
                } else {
                    $racesToDrop = $lowestRacesToDrop;
                }
                // $pointsRemovedByDrops = array_sum(array_slice($raceResults, -$racesToDrop)); //helpful to check if this is working
                $raceResults = array_slice($raceResults, 0, -$racesToDrop);
            }
        }
        $finalTotalPointsByDriver = [];
        // dd($raceResultsByDriver);
        foreach ($raceResultsByDriver as $driverName => $raceResults) {
            $finalTotalPointsByDriver[$driverName] = array_sum($raceResults);
        }
        return $finalTotalPointsByDriver;
    }

    private function applySessionScoring($sessionData, $seasonId) {
        $scoringQuery = Scoring::where('season_id', $seasonId)->get();
        $calculatedResults = $this->updateIntervalByPenalties($sessionData);
        $calculatedResults = $this->updateRacePoints($scoringQuery, $calculatedResults);
        return $calculatedResults;
    }

    private function updateRacePoints($scoringQuery, $sessionData) {
        $qualy_points = json_decode($scoringQuery[0]->qualifying, true);
        $heat_points = json_decode($scoringQuery[0]->heat, true);
        $consolation_points = json_decode($scoringQuery[0]->consolation, true);
        $feature_points = json_decode($scoringQuery[0]->feature, true);

        $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        foreach($sessionData as $key => $racer){
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
            if($racer->fastest_lap_points > 0 && $racer->simsession_name != "QUALIFY"){
                $racer->race_points += $racer->fastest_lap_points;
            }
        }
        return $sessionData;
    }

    private function updateIntervalByPenalties($sessionData){
        $mergedResultsForAllSessions = new Collection();
        $leadLappers = $sessionData->filter(function ($session) {
            return strpos($session['interval'], 'laps') == false;
        });
        $lappedDrivers = $sessionData->filter(function ($session) {
            return strpos($session['interval'], 'laps') == true;
        });
        $session_types = $sessionData->unique('simsession_name')->pluck('simsession_name');

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
        return $mergedResultsForAllSessions;
    }

    private function getRacePoints($driver, $scoringQuery, $race_session) {
        $qualy_points = json_decode($scoringQuery[0]->qualifying, true);
        $heat_points = json_decode($scoringQuery[0]->heat, true);
        $consolation_points = json_decode($scoringQuery[0]->consolation, true);
        $feature_points = json_decode($scoringQuery[0]->feature, true);
        $race_points = 0;
        switch ($race_session) {
            case 'QUALIFY':
                $race_points = $qualy_points[$driver->finish_position];
                break;
            case 'CONSOLATION':
                $race_points = $consolation_points[$driver->finish_position];
                break;
            case 'RACE':
            case 'FEATURE':
                $race_points = $feature_points[$driver->finish_position];
                break;
            default:
            if(str_contains($race_session, "HEAT")){
                    $race_points = $heat_points[$driver->finish_position];
                }
                break;
          }
          return $race_points;
      }
}

