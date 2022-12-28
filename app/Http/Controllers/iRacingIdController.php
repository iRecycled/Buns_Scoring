<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use iRacingPHP\iRacing;

class iRacingIdController extends Controller
{
    public function submit(Request $request){
        info('got the form' . $request->Racing_Id);
        $iracing = new iRacing('npeterson1996@gmail.com', env('IRACING_PASSWORD'));
        $summary = $iracing->stats->member_summary();
        print_r($summary);
        return view('temp');
    }
}
