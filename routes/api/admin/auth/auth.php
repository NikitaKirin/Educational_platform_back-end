<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('App\\Http\Controllers\\Api\\Admin')->group(function () {
    Route::namespace('Auth')->group(function () {
        Route::post('admin/login', 'LoginController');
        Route::post('admin/register', 'RegisterController');
    });
});
