<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('App\\Http\\Controllers\\Api\\User')->middleware('auth:api')->group(function () {
    Route::get('/user/home', 'HomeController');
});

Route::namespace('App\\Http\\Controllers\\Api')->middleware('auth:api')->group(function () {
    //Route::get('/users/{user}', 'UserController@show')->middleware('can:view,user'); // Посмотреть страницу профиля
    Route::get('/user/me', 'UserController@me'); // Посмотреть страницу своего профиля
    Route::patch('/user/me', 'UserController@update'); // Обновить данные своего профиля;
});
