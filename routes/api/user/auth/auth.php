<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api\User')->group(function () {
    Route::namespace('Auth')->group(function () {
        Route::post('login', 'LoginController')->middleware('guest');
        Route::post('register', 'RegisterController')->middleware('guest');
        Route::post('logout', 'LogoutController')->middleware('auth:api');
    });
});

