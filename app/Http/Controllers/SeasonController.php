<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use iRacingPHP\iRacing;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Intervention\Image\Size;
use PhpParser\Node\Expr\Cast\Bool_;

class SeasonController extends Controller
{
    public function createSeason(Request $request) {
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
            $scoring->enabled_percentage_laps = boolval($request->enabled_percentage_laps);
            $scoring->lap_percentage_to_complete = $request->lap_percentage_to_complete;
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
            $scoring->enabled_percentage_laps = boolval($request->enabled_percentage_laps);
            $scoring->lap_percentage_to_complete = $request->lap_percentage_to_complete;
            $scoring->save();
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
        $percentLapsValue = Scoring::select('enabled_percentage_laps')->where('season_id', $seasonId)->value('enabled_percentage_laps');
        $enabled_percentage_laps = $percentLapsValue ? true : false;
        $lap_percentage_to_complete = Scoring::select('lap_percentage_to_complete')->where('season_id', $seasonId)->value('lap_percentage_to_complete');

        return view('season.editScoring', compact('league', 'season', 'qualifying', 'heat', 'consolation', 'feature', 'fastest_lap', 'drop_week_enabled', 'drop_week_start', 'races_to_drop', 'enabled_percentage_laps', 'lap_percentage_to_complete'));
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

    public function newSessionSubmit(Request $request, $leagueId, $seasonId) {
        info($request->file('json_file'));
        if($request->file('json_file')){
          $file = $request->file('json_file');
          $json = $file->getContent();
          $data = json_decode($json);
        } else {
            return redirect()->back()->withErrors(['message' => 'Failed to load file']);
        }
        $sessionId = $data->subsession_id;
        $results = $data->session_results;
        $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        $sessionResultsToInsert = [];
          foreach($results as $result){
            //looping over race types (practice, qualy, heat, race, etc)
            if(preg_match($validSessionPattern, $result->simsession_name)){
                //looping over each driver in the session
                foreach($result->results as $driver){
                    $record = [
                        'subsession_id' => $sessionId,
                        'simsession_name' => $result->simsession_name,
                        'finish_position' => ++$driver->finish_position,
                        'race_points' => 0,
                        'display_name' => $driver->display_name,
                        'league_id' => $leagueId,
                        'season_id' => $seasonId,
                        'laps_lead' => $driver->laps_lead,
                        'laps_completed' => $driver->laps_complete,
                        'average_lap_time' => $this->convertTime($driver->average_lap),
                        'best_lap_time' => $this->convertTime($driver->best_lap_time),
                        'best_lap_number' => $driver->best_lap_num,
                        'qualifying_lap_time' => $this->convertTime($driver->best_qual_lap_time),
                        'starting_pos' => ++$driver->starting_position,
                        'interval' => $this->convertTime($driver->interval),
                        'incidents' => $driver->incidents,
                        'club_name' => $driver->club_name,
                        'license_category' => $data->license_category,
                        'corners_per_lap' => $data->corners_per_lap,
                        'track_name' => $data->track->track_name,
                        'config_name' => $data->track->config_name,
                        'temp_value' => $data->weather->temp_value,
                        'temp_units' => $data->weather->temp_units,
                        'rel_humidity' => $data->weather->rel_humidity,
                        'race_date' => Carbon::parse($data->start_time)->format('Y-m-d')
                    ];
                    $sessionResultsToInsert[] = $record;
                }
            }
          }
          Session::upsert($sessionResultsToInsert, [
            'subsession_id',
            'simsession_name',
            'finish_position',
            'race_points',
            'display_name',
            'league_id',
            'season_id',
            'laps_lead',
            'laps_completed',
            'average_lap_time',
            'best_lap_time',
            'best_lap_number',
            'qualifying_lap_time',
            'starting_pos',
            'interval',
            'incidents',
            'club_name',
            'license_category',
            'corners_per_lap',
            'track_name',
            'config_name',
            'temp_value',
            'temp_units',
            'rel_humidity',
            'race_date'
          ]);
        $sessionResults = $sessions = Session::where('subsession_id', $sessionId)
        ->get(['id', 'simsession_name', 'finish_position', 'interval', 'laps_completed', 'best_lap_time']);
        $this->setIntervalByLeader($sessionResults);
        $unique_types = $sessionResults->unique('simsession_name');
        $fastest_lap_points = Scoring::select('fastest_lap')->where('season_id', $seasonId)->get();
        foreach($unique_types as $key => $type) {
          $driver = $this->getFastestDriver($sessionResults, $type->simsession_name);
          $driver->fastest_lap_points = json_decode($fastest_lap_points[0]->fastest_lap);
          $driver->save();
        }
        $url = url('season/'. $seasonId);
        return redirect($url)->with(compact('leagueId'));
      }

    private function getFastestDriver($collection, $simsession_name) {
        return $collection->where('simsession_name', $simsession_name)
        ->filter(function ($item) {
            return ($item->best_lap_time != "-");
        })
        ->sortBy(function ($item) {
            if (strpos($item->best_lap_time, ':') != false) {
                [$minutes, $seconds] = explode(':', $item->best_lap_time);
                return $minutes * 60 + $seconds;
            } else {
                return $item->best_lap_time;
            }
        })->first();
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

      private function setIntervalByLeader($sessions) {
        $leadersLapsComplete = $sessions->filter(function ($driver) {
            return $driver->finish_position == 1;
        })->pluck('laps_completed', 'simsession_name');
        $cases = [];
        $ids = [];
        if ($leadersLapsComplete) {
            foreach ($sessions as $race) {
                if($race->finish_position != 1 && $race->interval == '-') {
                    $lapsBehind = $leadersLapsComplete[$race->simsession_name] - $race->laps_completed;
                    $interval = '-' . abs($lapsBehind) . ' laps';
                    $cases[] = "WHEN {$race->id} THEN '{$interval}'";
                    $ids[] = $race->id;
                }
            }
            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);
            DB::update("UPDATE sessions SET `interval` = CASE id {$cases} END WHERE id IN ({$ids})");
        }
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

    public function getiRacingSeasonData(Request $req) {
        $iRacingLeagueId = $req->iRacingLeagueId;
        $iracing = new iRacing('npeterson1996@gmail.com', 'haXyVVBYhsnNzcjGW2Mv09X//wyfimI2ccDL7YeIp9A=', true);
        $seasons = $iracing->league->seasons($iRacingLeagueId);
        $seasonNames = [];
        foreach($seasons->seasons as $season){
            $seasonNames[$season->season_id] = $season->season_name;
        }
        return $seasonNames;
    }

    public function updateSeasonWithiRacingSync(Request $req, $id) {
        $season = Season::where('id', $id)->first();
        $season->iracing_leagueId = $req->iRacingLeagueId;
        $season->iracing_seasonId = $req->iRacingSeasonId;
        $season->save();
        $this->createSessionsFromiRacingSync($req->iRacingLeagueId, $req->iRacingSeasonId);
        return redirect("season/" . $id)->with('success', 'Race has been imported');
    }

    public function createSessionsFromiRacingSync($leagueId, $seasonId) {
        $seasonToSync = Season::where('iracing_leagueId', $leagueId)->where('iracing_seasonId', $seasonId)->get();
        $iracing = new iRacing('npeterson1996@gmail.com', 'haXyVVBYhsnNzcjGW2Mv09X//wyfimI2ccDL7YeIp9A=', true);

        $sessionData = collect($seasonToSync)->flatMap(function ($season) use ($iracing) {
            // info('start iracing api request for season id '. $season->iracing_leagueId . " " . time());
            $allSessions = $iracing->league->season_sessions($season->iracing_leagueId, $season->iracing_seasonId, ['results_only' => true]);
            // info('after iracing api request ' . $season->iracing_leagueId . " " . time());
            return collect($allSessions->sessions)->map(function ($session) use ($season) {
                $subsessionId = $session->subsession_id;
                return [
                    $subsessionId => [
                        'season_id' => $season->id,
                        'league_id' => $season->league_id,
                        'iracing_subsession_id' => $session->subsession_id
                    ],
                ];
            });
        })->collapse();
        $subsessionIds = [];
        foreach($sessionData as $data) {
            $subsessionIds[] = $data['iracing_subsession_id'];
        }
        $subsessionIdsExist = Session::whereIn('subsession_id', $subsessionIds)
            ->select('id', 'league_id', 'subsession_id')
            ->get()
            ->keyBy('subsession_id');
        $filteredSessionData = collect($sessionData)->filter(function ($data) use ($subsessionIdsExist) {
            return isset($data['iracing_subsession_id']) && !$subsessionIdsExist->has($data['iracing_subsession_id']);
        })->all();
        foreach($filteredSessionData as $data) {
            $json = $iracing->results->get($data['iracing_subsession_id'], ['include_licenses' => false]);
            $this->newSessionSubmitFromAPI($json, $data['league_id'], $data['season_id']);
        }
    }

    public function newSessionSubmitFromAPI($json, $leagueId, $seasonId) {
        $data = $json;
        $sessionId = $data->subsession_id;
        $results = $data->session_results;

        $sessionId = $data->subsession_id;
        $results = $data->session_results;
        $validSessionPattern = '/^(QUALIFY|CONSOLATION|RACE|FEATURE|HEAT( \d+)?)$/';
        $sessionResultsToInsert = [];
          foreach($results as $result){
            //looping over race types (practice, qualy, heat, race, etc)
            if(preg_match($validSessionPattern, $result->simsession_name)){
                //looping over each driver in the session
                foreach($result->results as $driver){
                    $record = [
                        'subsession_id' => $sessionId,
                        'simsession_name' => $result->simsession_name,
                        'finish_position' => ++$driver->finish_position,
                        'race_points' => 0,
                        'display_name' => $driver->display_name,
                        'league_id' => $leagueId,
                        'season_id' => $seasonId,
                        'laps_lead' => $driver->laps_lead,
                        'laps_completed' => $driver->laps_complete,
                        'average_lap_time' => $this->convertTime($driver->average_lap),
                        'best_lap_time' => $this->convertTime($driver->best_lap_time),
                        'best_lap_number' => $driver->best_lap_num,
                        'qualifying_lap_time' => $this->convertTime($driver->best_qual_lap_time),
                        'starting_pos' => ++$driver->starting_position,
                        'interval' => $this->convertTime($driver->interval),
                        'incidents' => $driver->incidents,
                        'club_name' => $driver->club_name,
                        'license_category' => $data->license_category,
                        'corners_per_lap' => $data->corners_per_lap,
                        'track_name' => $data->track->track_name,
                        'config_name' => $data->track->config_name,
                        'temp_value' => $data->weather->temp_value,
                        'temp_units' => $data->weather->temp_units,
                        'rel_humidity' => $data->weather->rel_humidity,
                        'race_date' => Carbon::parse($data->start_time)->format('Y-m-d')
                    ];
                    $sessionResultsToInsert[] = $record;
                }
            }
          }
          Session::upsert($sessionResultsToInsert, [
            'subsession_id',
            'simsession_name',
            'finish_position',
            'race_points',
            'display_name',
            'league_id',
            'season_id',
            'laps_lead',
            'laps_completed',
            'average_lap_time',
            'best_lap_time',
            'best_lap_number',
            'qualifying_lap_time',
            'starting_pos',
            'interval',
            'incidents',
            'club_name',
            'license_category',
            'corners_per_lap',
            'track_name',
            'config_name',
            'temp_value',
            'temp_units',
            'rel_humidity',
            'race_date'
          ]);
          $sessionResults = Session::where('subsession_id', $sessionId)
          ->get(['id', 'simsession_name', 'finish_position', 'interval', 'laps_completed', 'best_lap_time']);
          $this->setIntervalByLeader($sessionResults);
          $unique_types = $sessionResults->unique('simsession_name');
          $fastest_lap_points = Scoring::select('fastest_lap')->where('season_id', $seasonId)->get();
          foreach($unique_types as $key => $type) {
            $driver = $this->getFastestDriver($sessionResults, $type->simsession_name);
            if($driver) {
                $driver->fastest_lap_points = json_decode($fastest_lap_points[0]->fastest_lap) || 0;
                $driver->save();
            }
          }
        }
}
