<?php
/*
use Illuminate\Support\Facades\Route;

Route::namespace('Admin')->middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/admin/home', 'HomeController')->name('admin.home');
    //    Route::post('/admin', );
});

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('admin/users/blocked', 'UserController@showBlocked')->middleware('can:showBlocked,App\\Models\\User')
         ->name('admin.users.show.blocked'); // Вывести список заблокированных пользователей;
    Route::get('/admin/users', 'UserController@index')->middleware('can:viewAny,App\\Models\\User')
         ->name('admin.users.index'); // Выводит список всех пользователей
    Route::post('/admin/users', 'UserController@store')->name('admin.users.store'); // Регистрирует нового пользователя
    Route::get('admin/users/{user}', 'UserController@show')->middleware('can:view,user')
         ->name('admin.users.show'); // Посмотреть страницу пользователя
    Route::patch('admin/users/{user}', 'UserController@updateSomeOneProfile')
         ->name('admin.users.update');// Обновляет данные профиля любого пользователя;
    Route::delete('admin/users/{user}/avatar', 'AvatarController@destroySomeOneAvatar')
         ->middleware('can:destroySomeOneAvatar,user')
         ->name('admin.users.avatar.delete'); //Удаляет аватар любого пользователя;
    Route::patch('admin/users/{user}/block', 'UserController@block')->middleware('can:block,user')
         ->name('admin.users.block'); // Заблокировать любого пользователя;
    Route::patch('admin/users/{user}/unblock', 'UserController@unblock')->middleware('can:unblock,user')
         ->name('admin.users.unblock'); // Разблокировать любого пользователя;
});*/
