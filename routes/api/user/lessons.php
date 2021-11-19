<?php

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/lessons', 'LessonController@index')->name('lesson.index'); // Получить список всех уроков;
    Route::post('/lessons', 'LessonController@store')->name('lesson.store'); // Создать новый урок;
});
