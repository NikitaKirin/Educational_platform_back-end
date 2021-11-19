<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Lesson\CreateLessonRequest;
use App\Http\Requests\Api\Lesson\UpdateLessonRequest;
use App\Http\Resources\LessonResourceCollection;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    // Вывести список всех уроков. Функционал любого пользователя.
    public function index(): LessonResourceCollection {
        return new LessonResourceCollection(Lesson::withCount('fragments')->paginate(6));
    }

    // Создать урок. Функционал учителя и ученика.
    public function store( CreateLessonRequest $request ) {
        DB::transaction(function () use ( $request ) {
            $lesson = new Lesson([
                'title'      => $request->input('title'),
                'annotation' => $request->input('annotation'),
                'user_id'    => Auth::user()->id,
            ]);
            $lesson->save();
            $fragments = $request->input('fragments');
            for ( $i = 0; $i < count($fragments); $i++ ) {
                if ( $lesson->fragments()->where('id', $fragments[$i])->exists() )
                    continue;
                $lesson->fragments()->attach($fragments[$i], ['order' => $i + 1]);
            }
            $lesson->tags()->sync($request->input('tags'));
            $lesson->save();
        });

        return response([
            'messages' => 'Урок успешно создан!',
        ], 201);
    }

    // Обновить данные урока. Функционал любого пользователя.
    public function update( UpdateLessonRequest $request, Lesson $lesson ) {
        DB::transaction(function () use ( $request, $lesson ) {
            $lesson->update(['title' => $request->input('title'), 'annotation' => $request->input('annotation')]);
            $lesson->fragments()->sync([]);
            $fragments = $request->input('fragments');
            for ( $i = 0; $i < count($fragments); $i++ ) {
                if ( $lesson->fragments()->where('id', $fragments[$i])->exists() )
                    continue;
                $lesson->fragments()->attach($fragments[$i], ['order' => $i + 1]);
            }
        });
        return response([
            'messages' => 'Урок успешно обновлен!',
        ]);
    }

    public function destroy( Request $request, Lesson $lesson ) {
        if ( $lesson->delete() )
            return response(['message' => 'Урок успешно удалён']);
        return response(['message' => 'Произошла ошибка при удалении'], 400);
    }
}


