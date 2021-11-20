<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Lesson\CreateLessonRequest;
use App\Http\Requests\Api\Lesson\UpdateLessonRequest;
use App\Http\Resources\FragmentResourceCollection;
use App\Http\Resources\LessonResourceCollection;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    // Вывести список всех уроков. Функционал пользователя и администратора.
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

    // Обновить данные урока. Функционал пользователя и администратора.
    public function update( UpdateLessonRequest $request, Lesson $lesson ) {
        DB::transaction(function () use ( $request, $lesson ) {
            $lesson->update(['title' => $request->input('title'), 'annotation' => $request->input('annotation')]);
            $lesson->fragments()->sync([]);
            $fragments = $request->input('fragments');
            $tags = $request->input('tags');
            for ( $i = 0; $i < count($fragments); $i++ ) {
                if ( $lesson->fragments()->where('id', $fragments[$i])->exists() )
                    continue;
                $lesson->fragments()->attach($fragments[$i], ['order' => $i + 1]);
            }
            $lesson->tags()->sync($tags);
        });
        return response([
            'messages' => 'Урок успешно обновлен!',
        ]);
    }

    // Удалить фрагмент (мягкое удаление). Функционал пользователя и администратора.
    public function destroy( Request $request, Lesson $lesson ) {
        if ( $lesson->delete() )
            return response(['message' => 'Урок успешно удалён']);
        return response(['message' => 'Произошла ошибка при удалении'], 400);
    }

    // Добавить/удалить фрагмент из избранного. Функционал пользователя и администратора.
    public function like( Request $request, Lesson $lesson ) {
        $query = DB::table('lesson_user')->where('lesson_id', $lesson->id)->where('user_id', Auth::id());
        if ( $query->exists() )
            $query->delete();
        else
            Auth::user()->favouriteLessons()->attach($lesson->id);
        return response(['message' => 'Ok'], 200);
    }

    // Получить список избранных фрагментов. Функционал пользователя и администратора.
    public function likeIndex( Request $request ): LessonResourceCollection {
        $title = $request->input('title');
        $tags = $request->input('tags');
        $lessons = Auth::user()->favouriteLessons()->withCount(['tags', 'fragments'])->with('tags')
                       ->when($title, function ( $query ) use ( $title ) {
                           return $query->where('title', 'ILIKE', '%' . $title . '%');
                       })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIn('tag_id', $tags);
                });
            });

        return new LessonResourceCollection($lessons->orderBy('title')->paginate(6));
    }
}


