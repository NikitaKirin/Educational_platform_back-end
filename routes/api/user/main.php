<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('App\\Http\\Controllers\\Api\\User')->middleware('auth:api')->group(function () {
    Route::get('/user/home', 'HomeController');
});

Route::namespace('App\\Http\\Controllers\\Api')->middleware('auth:api')->group(function () {
    Route::get('/user/me', 'UserController@me'); // Посмотреть страницу своего профиля
    Route::patch('/user/me', 'UserController@update'); // Обновить данные своего профиля;
    Route::post('/user/me/avatar', 'AvatarController@updateOwnAvatar'); // Загрузить новый аватар своего профиля;
    Route::delete('/user/me/avatar', 'AvatarController@destroyOwnAvatar'); // Удалить свой аватар;
});
