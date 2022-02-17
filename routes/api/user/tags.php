<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/tags', 'TagController@index')->name('tags.index'); // Получить список всех тегов;
    Route::get('/tags/{tag}', 'TagController@show');
});
