<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});
Route::get('/home', function() {
    return view('home');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/league/createLeague', '\App\Http\Controllers\LeagueController@createLeague')->name('createLeague');
Route::post('/league/{leagueId}/createSeason', '\App\Http\Controllers\SeasonController@createSeason')->name('createSeason');
Route::post('/league/{leagueId}/{seasonId}', '\App\Http\Controllers\SeasonController@newSessionSubmit');
Route::post('season/{id}/scoring', '\App\Http\Controllers\SeasonController@updateScoring');
Route::post('/season/{id}/delete/{sessionId}', '\App\Http\Controllers\SeasonController@deleteSession');
Route::post('/session/{sessionId}', '\App\Http\Controllers\SessionController@submitPenalties');

Route::get('/league/create-league', function() {return view('league.create_league');})->name('create_league');
Route::get('/league/{leagueId}/create-season', '\App\Http\Controllers\SeasonController@create_season')->name('create_season');
Route::get('league/{leagueId}', '\App\Http\Controllers\LeagueController@showLeague')->name('league.showLeague');
Route::get('session/{sessionId}', '\App\Http\Controllers\SessionController@showSession');
Route::get('season/{id}', '\App\Http\Controllers\SeasonController@showSeason')->name('season.showSeason');
Route::get('season/{id}/scoring', '\App\Http\Controllers\SeasonController@editScoring');
Route::get('season/{id}/standings', '\App\Http\Controllers\SeasonController@showStandings');

require __DIR__.'/auth.php';
