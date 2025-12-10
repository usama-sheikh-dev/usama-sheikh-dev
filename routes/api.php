<?php

use App\Http\Controllers\APIs\AuthController;
use App\Http\Controllers\APIs\ContestController;
use App\Http\Controllers\APIs\ContestParticipationController;
use App\Http\Controllers\APIs\LeaderboardController;
use Illuminate\Support\Facades\Route;

/*/:::::::::::::::::: Authentication APIs (Admin, VIP, Normal & Guest User)  ::::::::::::::::::/*/
Route::group(['prefix' => 'user'], function () {

    Route::post('login', [AuthController::class, 'loginUser']);
    Route::post('register', [AuthController::class, 'registerUser']);

    Route::post('forget/password', [AuthController::class, 'forgetPassword']);
    Route::post('token/verification', [AuthController::class, 'tokenVerification']);
    Route::post('reset/password', [AuthController::class, 'resetPassword']);

    Route::get('logout', [AuthController::class, 'logoutUser'])->middleware('auth:api');
});
/*-----------------------------------------------------------------------------------------------*/

/*/:::::::::::::::::: Contests API ::::::::::::::::::/*/
Route::group(['middleware' => ['auth:api'], 'prefix' => 'user'], function () {
    Route::get('contests', [ContestController::class, 'index']);
    Route::get('contests/{id}', [ContestController::class, 'show']);
});
/*-----------------------------------------------------------------------------------------------*/

/*/:::::::::::::::::: Participants API ::::::::::::::::::/*/
Route::middleware(['auth:sanctum', 'role:participant'])->group(function () {
    Route::post('/contests/{id}/join', [ContestParticipationController::class, 'join']);
    Route::post('/contests/{id}/submit', [ContestParticipationController::class, 'submit']);
});
/*-----------------------------------------------------------------------------------------------*/

/*/:::::::::::::::::: Leaderboard API ::::::::::::::::::/*/
Route::post('show/ranking', [LeaderboardController::class, 'index']);


/*/:::::::::::::::::: User History API ::::::::::::::::::/*/
//contests joined

//score

//prizes
