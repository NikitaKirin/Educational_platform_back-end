<?php

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/fragments', 'FragmentController@index')->name('fragments.index'); // Посмотреть список всех фрагментов;
    Route::post('/fragments', 'FragmentController@store')->name('fragments.store'); // Создать новый фрагмент;
    Route::get('/fragments/{fragment}', 'FragmentController@show')
         ->name('fragments.show'); // Получить данные об определенном фрагменте;
    Route::patch('/fragments/{fragment}', 'FragmentController@update')->middleware('can:update,fragment')
         ->name('fragments.update'); // Обновить данные фрагмента;
    Route::delete('/fragments/{fragment}', 'FragmentController@destroy')->middleware('can:delete,fragment')
         ->name('fragments.destroy'); // Удалить фрагмент;
});
