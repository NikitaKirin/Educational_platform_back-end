<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\\Http\\Controllers\\Api\\Admin')->middleware('auth:api')->group(function () {
    Route::get('/admin/home', 'HomeController')->name('admin.home');
    //    Route::post('/admin', );
});

Route::namespace('App\\Http\\Controllers\\Api')->middleware('auth:api')->group(function () {
    Route::get('/admin/users', 'UserController@index')->middleware('can:viewAny,App\\Models\\User')
         ->name('admin.users.index'); // Выводит список всех пользователей
    Route::post('/admin/users', 'UserController@store')->name('admin.users.store'); // Регистрирует нового пользователя
    Route::get('admin/users/{user}', 'UserController@show')->middleware('can:view,user')
         ->name('admin.users.show'); // Посмотреть страницу пользователя
    Route::patch('admin/users/{user}', 'UserController@updateSomeOneProfile')
         ->name('admin.users.update');// Обновляет данные профиля любого пользователя;
    Route::delete('admin/users/{user}/avatar', 'AvatarController@destroySomeOneAvatar')
         ->middleware('can:destroySomeOneAvatar,user')
         ->name('admin.users.avatar.update'); //Обновляет аватар любого пользователя;
});
