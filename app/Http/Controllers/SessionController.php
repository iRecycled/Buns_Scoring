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
        foreach($unique_types as $type) {
            $types[] = $type->simsession_name;
        }
        return view('session.session', compact(
            'sessions',
            'sessionId',
            'types',
            'league',
            'season_id'
        ));
      }

    public function getSeason($subsession_id){
        $season_id = Session::select('season_id')->where('subsession_id', $subsession_id)->first();
        return redirect()->route('season.showSeason', ['id', $season_id]);
    }
}
