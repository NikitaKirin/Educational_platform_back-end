<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('App\\Http\\Controllers\\Api\\User')->middleware('auth:api')->group(function () {
    Route::post('/user', function () {
        return response()->json([
            'message' => 'Главная страница для авторизованных пользователей',
        ]);
    });
});
