<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'blockUser'])->group(function () {
    Route::get('/teacher/fragments/{user}', 'FragmentController@fragmentsTeacherIndex')
         ->middleware('can:viewTeacherFragments,user')
         ->name('fragments.teacher.index'); // Получить список фрагментов определённого учителя;
    Route::get('/fragments/like/{title?}/{type?}/{tags?}', 'FragmentController@likeIndex')
         ->name('fragments.like.index'); // Получить список избранных фрагментов (текущий пользователь);
    Route::post('/fragments', 'FragmentController@store')->middleware('can:create,App\\Models\\Fragment')
         ->name('fragments.store'); // Создать новый фрагмент;
    Route::get('/fragments/{fragment}', 'FragmentController@show')
         ->name('fragments.show'); // Получить данные об определенном фрагменте;
    Route::patch('/fragments/{fragment}', 'FragmentController@update')->middleware('can:update,fragment')
         ->name('fragments.update'); // Обновить данные фрагмента;
    Route::delete('/fragments/{fragment}', 'FragmentController@destroy')->middleware('can:delete,fragment')
         ->name('fragments.destroy'); // Удалить фрагмент;
    Route::get('/my-fragments/{title?}/{type?}/{tags?}', 'FragmentController@myIndex')
         ->name('fragments.index.my'); // Посмотреть список фрагментов текущего пользователя;
    Route::get('/fragments/{title?}/{type?}/{tags?}', 'FragmentController@index')
         ->name('fragments.index'); // Посмотреть список всех фрагментов;
    Route::put('/fragments/{fragment}', 'FragmentController@like')->middleware('can:like,App\\Models\\Fragment')
         ->name('fragments.like'); // Добавить/удалить фрагмент из избранного;
});
