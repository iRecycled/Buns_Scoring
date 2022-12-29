<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\League;

class LeagueController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:leagues',
            'description' => 'required',
        ]);

        $league = new League();
        $league->name = $request->name;
        $league->description = $request->description;
        if($league->save()){
            return redirect()->route('create')->with('success', 'League created successfully');
        } else {
            return redirect()->route('create')->withErrors(['message' => 'League failed to create']);
        }
    }
}
