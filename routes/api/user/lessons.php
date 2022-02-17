<?php


use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/lessons/{lesson}', 'LessonController@show')->name('lesson.show')
         ->where('lesson', '[0-9]+'); // Посмотреть текущий урок;
    Route::get('lessons/like/{title?}/{tags?}/{creator?}', 'LessonController@likeIndex')
         ->name('lesson.like.index'); // Получить список избранных уроков текущего пользователя;
    Route::get('/lessons/{title?}/{tags?}/{creator?}', 'LessonController@index')
         ->name('lesson.index'); // Получить список всех уроков;
    Route::get('my-lessons', 'LessonController@myIndex')
         ->name('lesson.index.my'); //Получить список уроков текущего авторизованного пользователя;
    Route::get('teacher/lessons/{user}', 'LessonController@lessonsTeacherIndex')->name('lesson.teacher.index')
         ->middleware('can:viewTeacherLessons,user'); // Получить список уроков определенного учителя;
    Route::post('/lessons', 'LessonController@store')->middleware('can:create,App\\Models\\Lesson')
         ->name('lesson.store'); // Создать новый урок;
    Route::patch('/lessons/{lesson}', 'LessonController@update')->middleware('can:update,lesson')
         ->name('lesson.update'); // Обновить урок;
    Route::delete('/lessons/{lesson}', 'LessonController@destroy')->middleware('can:delete,lesson')
         ->name('lesson.destroy'); // Удалить урок;
    Route::put('/lessons/{lesson}', 'LessonController@like')->middleware('can:like,lesson')
         ->name('lesson.like'); // Добавить/удалить урок из избранного;
});
