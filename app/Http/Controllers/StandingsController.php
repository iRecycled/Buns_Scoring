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
            $unique_types = $session->unique('simsession_name');
            foreach($unique_types as $type) {
                $types[] = $type->simsession_name;
            }
            //key value pair for session_name and lead drivers laps completed
            foreach($types as $session_name) {
                $sessionTypesWithRaceLaps[$session_name] = $session->filter(function ($driver) use ($session_name) {
                    return $driver->simsession_name == $session_name && $driver->finish_position == 1;
                })->first()->laps_completed;
            }
            $tempStanding = $this->applySessionScoring($session, $seasonId, $sessionTypesWithRaceLaps);
            $standings = $standings->concat($tempStanding);
        }

        $scoringQuery = Scoring::where('season_id', $seasonId)->get();
        $dropWeeksEnabled = boolval($scoringQuery[0]->enabled_drop_weeks);
        if($dropWeeksEnabled){
            $totalPointsByDriver = $this->applyDropWeeks($standings, $scoringQuery);
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
            $fastestLaps = $sessions->where('simsession_name', '!=', "QUALIFY")->whereNotNull('fastest_lap_points')->pluck('fastest_lap_points');
            $NoDroptotalPoints = $sessions->sum('race_points');
            $totalPointsWithDrop = $sessions->first()->total_points;
            $pointsDropped = $NoDroptotalPoints - $totalPointsWithDrop;
            return [
                'display_name' => $displayName,
                'total_points' => isset($sessions->first()->total_points) ? $sessions->first()->total_points/* + $fastestLaps->sum()*/ : $sessions->sum('race_points')/* + $fastestLaps->sum()*/,
                'total_laps' => $sessions->sum('laps_completed'),
                'total_incidents' => $sessions->sum('incidents'),
                'total_races' => $totalRaces,
                'fastest_laps' => count($fastestLaps),
                'total_lead' => $sessions->sum('laps_lead'),
                'total_wins' => $sessions->where('finish_position', 1)->where('simsession_name', '!=', 'QUALIFY')->count(),
                'races_dropped' => $racesToDrop,
                'points_dropped' => $pointsDropped
            ];
        });
        $standings = $standings->sortByDesc('total_points');
        // $standings = $standings->sortByDesc('total_points')->values();
        $league = $seasons->first()->league;

        return view ('season.standings.standings', compact('seasonId', 'standings', 'league','dropWeeksEnabled'));
    }

    private function applyDropWeeks($standings, $scoringQuery){
        $sessions = $standings->filter(function ($sesh) {
            return !str_contains($sesh->simsession_name, "QUALIFY");
        });
        $qualySessions = $standings->filter(function ($sesh) {
            return str_contains($sesh->simsession_name, "QUALIFY");
        });
        $startOfDropWeekScoring = $scoringQuery[0]->drop_weeks_start;
        $lowestRacesToDrop = $scoringQuery[0]->races_to_drop;
        $raceResultsByDriver = [];
        $qualyResultsByDriver = [];
        foreach ($sessions as $session) {
            $driverName = $session['display_name'];
            $racePoints = (int)$session['race_points'];

            if (!isset($raceResultsByDriver[$driverName])) {
                $raceResultsByDriver[$driverName] = [];
            }
            $raceResultsByDriver[$driverName][] = $racePoints;
        }
        foreach ($qualySessions as $session) {
            $driverName = $session['display_name'];
            $racePoints = (int) $session['race_points'];
            if(!isset($qualyResultsByDriver[$driverName])) {
                $qualyResultsByDriver[$driverName][] = [];
            }
            $qualyResultsByDriver[$driverName][] = $racePoints;
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
        foreach ($qualyResultsByDriver as $driverName => $raceResults) {
            $finalTotalPointsByDriver[$driverName] += array_sum($raceResults);
        }
        return $finalTotalPointsByDriver;
    }

    private function applySessionScoring($sessionData, $seasonId, $sessionTypesWithRaceLaps) {
        $scoringQuery = Scoring::where('season_id', $seasonId)->get();
        $calculatedResults = $this->updateIntervalByPenalties($sessionData);
        $calculatedResults = $this->updateRacePoints($scoringQuery, $calculatedResults, $sessionTypesWithRaceLaps);
        return $calculatedResults;
    }

    private function updateRacePoints($scoringQuery, $sessionData, $sessionTypesWithRaceLaps) {
        $qualy_points = json_decode($scoringQuery[0]->qualifying, true);
        $heat_points = json_decode($scoringQuery[0]->heat, true);
        $consolation_points = json_decode($scoringQuery[0]->consolation, true);
        $feature_points = json_decode($scoringQuery[0]->feature, true);

        $percentLapsEnabled = $scoringQuery[0]->enabled_percentage_laps;
        $percentLapsValue = $scoringQuery[0]->lap_percentage_to_complete;

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
            $minLaps = (int) $sessionTypesWithRaceLaps[$racer->simsession_name] * ($percentLapsValue / 100);
            if($percentLapsEnabled && $racer->laps_completed < $minLaps && $racer->simsession_name != "QUALIFY") {
                $racer->race_points = 0;
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
}

