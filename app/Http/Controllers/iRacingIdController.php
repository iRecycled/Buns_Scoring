<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use iRacingPHP\iRacing;

class iRacingIdController extends Controller
{
    private function hashLogin(string $username, string $password)
    {
        $concat = mb_convert_encoding($password . strtolower($username), 'UTF-8');
        $hash = hash('sha256', $concat, true);
        return base64_encode($hash);
    }

    public function submit(Request $request){
        info('got the form' . $request->Racing_Id);
        $hashedPW = $this->hashLogin('npeterson1996@gmail.com', env('IRACING_PASSWORD'));
        $iracing = new iRacing('npeterson1996@gmail.com', $hashedPW, true);
        $summary = $iracing->stats->member_summary();
        print_r($summary);
        return view('temp');
    }

}
