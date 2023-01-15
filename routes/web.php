<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/temp', function() {
    return view('temp');
})->middleware(['auth', 'verified'])->name('temp');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/league/create', function() {
    return view('league.create_league');
});

Route::post('/league/create', '\App\Http\Controllers\LeagueController@create')->name('create');
Route::post('/league/{leagueId}', '\App\Http\Controllers\LeagueController@leagueSessionSubmit')->name('leagueSessionSubmit');
Route::get('league/{leagueId}', '\App\Http\Controllers\LeagueController@showLeague')->name('league.showLeague');
Route::get('session/{sessionId}', '\App\Http\Controllers\LeagueController@showSession')->name('session.showSession');
Route::get('season/{id}', '\App\Http\Controllers\LeagueController@showSeason')->name('season.showSeason');

require __DIR__.'/auth.php';
