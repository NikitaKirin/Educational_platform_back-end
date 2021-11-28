<?php

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/lessons/{lesson}', 'LessonController@show')->name('lesson.show')
         ->where('lesson', '[0-9]+'); // Посмотреть текущий урок;
    Route::get('lessons/like/{title?}/{tags?}/{creator?}', 'LessonController@likeIndex')
         ->name('lesson.like.index'); // Получить список избранных уроков текущего пользователя;
    Route::get('/lessons/{title?}/{tags?}/{creator?}', 'LessonController@index')
         ->name('lesson.index'); // Получить список всех уроков;
    Route::post('/lessons', 'LessonController@store')->middleware('can:create,App\\Models\\Lesson')
         ->name('lesson.store'); // Создать новый урок;
    Route::patch('/lessons/{lesson}', 'LessonController@update')->middleware('can:update,lesson')
         ->name('lesson.update'); // Обновить урок;
    Route::delete('/lessons/{lesson}', 'LessonController@destroy')->middleware('can:delete,lesson')
         ->name('lesson.destroy'); // Удалить урок;
    Route::put('/lessons/{lesson}', 'LessonController@like')->middleware('can:like,lesson')
         ->name('lesson.like'); // Добавить/удалить урок из избранного;
});
