<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Session;
use App\Models\Season;
use App\Models\Scoring;
use Exception;
use iRacingPHP\iRacing;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ErrorHandler\Debug;

use function PHPUnit\Framework\isEmpty;

class LeagueController extends Controller
{
    public function createLeague(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:leagues',
            'description'
            // 'leagueId' => 'required|unique:leagues',
        ]);

        $league = new League();
        $league->name = $request->name;
        $league->description = "";
        if($request->description){
          $league->description = $request->description;
        }
        $league->league_owner_id = $request->user_id;

        try {
            if($league->save())
            {
              return redirect()->route('league.showLeague', ['leagueId' => $league->id])->with('success', 'League created successfully');
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

    public function deleteSeason(Request $req) {
        try {
            $leagueOwnerId = League::where('leagueId', $req->leagueId)->pluck('league_owner_id');
            DB::beginTransaction();
            if($leagueOwnerId && $leagueOwnerId[0] == $req->userId) {
                Season::where('league_id', $req->leagueId)->where('id', $req->seasonId)->delete();
                Scoring::where('league_id', $req->leagueId)->where('id', $req->seasonId)->delete();
                Session::where('league_id', $req->leagueId)->where('id', $req->seasonId)->delete();
            }
            DB::commit();
            return redirect("/league/". $req->leagueId)->with('success', 'League deleted successfully');
        } catch(Exception $e) {
            return $this->showLeague($req->leagueId);
        }
    }

    public function deleteLeague(Request $req) {
        try {
            $leagueOwnerId = League::where('leagueId', $req->leagueId)->pluck('league_owner_id');
            DB::beginTransaction();
            if($leagueOwnerId && $leagueOwnerId[0] == $req->userId) {
                League::where('leagueId', $req->leagueId)->delete();
                Season::where('league_id', $req->leagueId)->delete();
                Scoring::where('league_id', $req->leagueId)->delete();
                Session::where('league_id', $req->leagueId)->delete();
            }
            DB::commit();
            return redirect("")->with('success', 'League deleted successfully');
        } catch(Exception $e) {
            return $this->showLeague($req->leagueId);
        }
    }
}
