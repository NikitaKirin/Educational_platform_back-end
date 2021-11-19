<?php

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/lessons', 'LessonController@index')->name('lesson.index'); // Получить список всех уроков;
    Route::post('/lessons', 'LessonController@store')->name('lesson.store'); // Создать новый урок;
    Route::patch('/lessons/{lesson}', 'LessonController@update')->name('lesson.update'); // Обновить урок;
    Route::delete('/lessons/{lesson}', 'LessonController@destroy')->name('lesson.destroy'); // Удалить урок;
});
