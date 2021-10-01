<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\\Http\\Controllers\\Api\\Admin')->middleware('auth:api')->group(function () {
    Route::post('/admin', 'HomeController');
    Route::get('/admin/users', 'UserController');
});
