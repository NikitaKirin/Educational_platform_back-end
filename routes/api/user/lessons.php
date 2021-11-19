<?php

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/lessons', 'LessonController@index')->name('lesson.index'); // Получить список всех уроков;
    Route::post('/lessons', 'LessonController@store')->middleware('can:create,App\\Models\\Lesson')
         ->name('lesson.store'); // Создать новый урок;
    Route::patch('/lessons/{lesson}', 'LessonController@update')->middleware('can:update,lesson')
         ->name('lesson.update'); // Обновить урок;
    Route::delete('/lessons/{lesson}', 'LessonController@destroy')->middleware('can:delete,lesson')
         ->name('lesson.destroy'); // Удалить урок;
});
