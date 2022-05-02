<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Lesson\CreateLessonRequest;
use App\Http\Requests\Api\Lesson\UpdateLessonRequest;
use App\Http\Resources\FragmentResourceCollection;
use App\Http\Resources\LessonResource;
use App\Http\Resources\LessonResourceCollection;
use App\Models\AgeLimit;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    /**
     * Get all lessons
     * Получить все уроки доступные на платформе
     * @param Request $request
     * @return LessonResourceCollection
     */
    public function index( Request $request ): LessonResourceCollection {
        $title = $request->input('title');
        $creator = $request->input('creator');
        $tags = $request->input('tags');
        $ageLimit = $request->input('ageLimit');
        $lessons = Lesson::with('tags')->withCount(['tags', 'fragments'])
                         ->whereHas('user', function ( Builder $query ) {
                             $query->where('role', '<>', 'student');
                         })->when($title, function ( $query ) use ( $title ) {
                return $query->where('title', 'ILIKE', "%{$title}%");
            })->when($creator, function ( $query ) use ( $creator ) {
                return $query->whereHas('user', function ( $query ) use ( $creator ) {
                    $query->where('name', 'ILIKE', "%{$creator}%");
                });
            })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            })->when($ageLimit, function ( $query ) use ( $ageLimit ) {
                return $query->whereHas('ageLimit', function ( $query ) use ( $ageLimit ) {
                    $query->where('id', 'LIKE', $ageLimit);
                });
            });
        return new LessonResourceCollection($lessons->paginate(6));
    }

    // Создать урок. Функционал учителя и ученика.
    public function store( CreateLessonRequest $request ) {
        DB::transaction(function () use ( $request ) {
            $lesson = new Lesson([
                'title'      => $request->input('title'),
                'annotation' => $request->input('annotation'),
                'user_id'    => Auth::user()->id,
            ]);
            $lesson->ageLimit()->associate(AgeLimit::find($request->input('ageLimit')));
            $lesson->save();
            $fragments = $request->input('fragments');
            for ( $i = 0; $i < count($fragments); $i++ ) {
                if ( $lesson->fragments()->where('id', $fragments[$i])->exists() )
                    continue;
                $lesson->fragments()->attach($fragments[$i], ['order' => $i + 1]);
            }
            $lesson->tags()->sync($request->input('tags'));
            $lesson->save();
            if ( isset($request->fon) )
                $lesson->addMediaFromRequest('fon')->toMediaCollection('lessons_fons', 'lessons_fons');
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
            $lesson->ageLimit()->associate($request->input('ageLimit'));
            $lesson->save();
            $fragments = $request->input('fragments');
            $tags = $request->input('tags');
            for ( $i = 0; $i < count($fragments); $i++ ) {
                if ( $lesson->fragments()->where('id', $fragments[$i])->exists() )
                    continue;
                $lesson->fragments()->attach($fragments[$i], ['order' => $i + 1]);
            }
            $lesson->tags()->sync($tags);

            if ( $request->hasFile('fon') ) {
                if ( empty($lesson->getFirstMediaUrl('lessons_fons')) )
                    $lesson->addMediaFromRequest('fon')->toMediaCollection('lessons_fons', 'lessons_fons');
                else {
                    $lesson->clearMediaCollection('lessons_fons');
                    $lesson->addMediaFromRequest('fon')->toMediaCollection('lessons_fons', 'lessons_fons');
                }
            }
        });
        return response([
            'messages' => 'Урок успешно обновлен!',
        ]);
    }

    // Посмотреть текущий урок. Функционал любого пользователя.
    /*public function show( Request $request, Lesson $lesson ): FragmentResourceCollection {
        return new FragmentResourceCollection($lesson->fragments()->with('tags')->orderBy('order')->paginate(1));
    }*/

    public function show( Request $request, Lesson $lesson ): LessonResource {
        return new LessonResource($lesson->load('fragments')->load('tags'));
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

    /**
     * Get fragments which has like
     * Получить список избранных фрагментов
     * @param Request $request
     * @return LessonResourceCollection
     */
    public function likeIndex( Request $request ): LessonResourceCollection {
        $title = $request->input('title');
        $creator = $request->input('creator');
        $tags = $request->input('tags');
        $ageLimit = $request->input('ageLimit');
        $lessons = Auth::user()->favouriteLessons()->withCount(['tags', 'fragments'])->with('tags')
                       ->when($title, function ( $query ) use ( $title ) {
                           return $query->where('title', 'ILIKE', "%{$title}%");
                       })->when($creator, function ( $query ) use ( $creator ) {
                return $query->whereHas('user', function ( $query ) use ( $creator ) {
                    $query->where('name', 'ILIKE', "%{$creator}%");
                });
            })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            })->when($ageLimit, function ( $query ) use ( $ageLimit ) {
                return $query->whereHas('ageLimit', function ( $query ) use ( $ageLimit ) {
                    $query->where('id', 'LIKE', $ageLimit);
                });
            });

        return new LessonResourceCollection($lessons->orderBy('title')->paginate(6));
    }

    /**
     * Get current auth user's lessons
     * Получить список уроков текущего авторизованного пользователя
     * @param Request $request
     * @return LessonResourceCollection
     */
    public function myIndex( Request $request ): LessonResourceCollection {
        $title = $request->input('title');
        $tags = $request->input('tags');
        $ageLimit = $request->input('ageLimit');
        $lessons = Auth::user()->lessons()->with('tags')->withCount(['tags', 'fragments'])
                       ->when($title, function ( $query ) use ( $title ) {
                           return $query->where('title', $title);
                       })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            })->when($ageLimit, function ( $query ) use ( $ageLimit ) {
                return $query->whereHas('ageLimit', function ( $query ) use ( $ageLimit ) {
                    $query->where('id', 'LIKE', $ageLimit);
                });
            });
        return new LessonResourceCollection($lessons->orderBy('title')->paginate(6));
    }

    // Получить список уроков определенного преподавателя.
    public function lessonsTeacherIndex( Request $request, User $user ): LessonResourceCollection {
        return new LessonResourceCollection($user->lessons()->with('tags')->withCount(['tags', 'fragments'])
                                                 ->paginate(6));
    }
}


