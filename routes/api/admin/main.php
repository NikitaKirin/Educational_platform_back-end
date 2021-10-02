<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\\Http\\Controllers\\Api\\Admin')->middleware('auth:api')->group(function () {
    Route::get('/admin/home', 'HomeController');
    //    Route::post('/admin', );
});

Route::namespace('App\\Http\\Controllers\\Api')->middleware('auth:api')->group(function () {
    Route::get('/admin/users', 'UserController@index')->middleware('can:viewAny,App\\Models\\User');
    Route::post('/admin/users', 'UserController@store');
});
