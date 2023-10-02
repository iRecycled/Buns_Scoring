<?php

namespace App\Http\Controllers;
use App\Models\Session;

use Illuminate\Http\Request;

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
        ->whereNotNull('penalty_points')->get();
        foreach($unique_types as $type) {
            $types[] = $type->simsession_name;
        }

        return view('session.session', compact(
            'sessions',
            'sessionId',
            'types',
            'league',
            'season_id',
            'drivers',
            'currentData'
        ));
    }

    public function submitPenalties(Request $request, $sessionId){
        $data = $request->all();
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
        return $this->showSession($sessionId);
    }

    public function getSeason($subsession_id){
        $season_id = Session::select('season_id')->where('subsession_id', $subsession_id)->first();
        return redirect()->route('season.showSeason', ['id', $season_id]);
    }
}
