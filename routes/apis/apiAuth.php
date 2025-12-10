<?php

use App\Http\Controllers\APIs\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin, VIP, Normal User Authentication API Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'user'], function () {

    Route::post('login', [AuthController::class, 'loginUser']);
    Route::post('register', [AuthController::class, 'registerUser']);

    Route::post('forget/password', [AuthController::class, 'forgetPassword']);
    Route::post('token/verification', [AuthController::class, 'tokenVerification']);
    Route::post('reset/password', [AuthController::class, 'resetPassword']);

    Route::get('logout', [AuthController::class, 'logoutUser'])->middleware('auth:api');
});
