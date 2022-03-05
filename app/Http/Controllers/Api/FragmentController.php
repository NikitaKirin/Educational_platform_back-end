<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Fragment\IndexFragmentRequest;
use App\Http\Requests\Api\Fragment\UpdateFragmentRequest;
use App\Http\Resources\FragmentResource;
use App\Http\Resources\FragmentResourceCollection;
use App\Models\Article;
use App\Models\Game;
use App\Models\Image;
use App\Models\Tag;
use App\Models\Test;
use App\Models\Fragment;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FragmentController extends Controller
{
    // Вывести список фрагментов текущего пользователя.
    public function myIndex( IndexFragmentRequest $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $fragments = Fragment::with('tags')->withCount('tags')->where('user_id', Auth::id())
                             ->when($title, function ( $query ) use ( $title ) {
                                 return $query->where('title', 'ILIKE', '%' . $title . '%');
                             })->when($type, function ( $query ) use ( $type ) {
                return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
            })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            });
        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    // Вывести список всех фрагментов. Функционал пользователя и администратора.
    public function index( IndexFragmentRequest $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $fragments = Fragment::with('tags')->withCount('tags')->when($title, function ( $query ) use ( $title ) {
            return $query->where('title', 'ILIKE', '%' . $title . '%');
        })->when($type, function ( $query ) use ( $type ) {
            return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
        })->when($tags, function ( $query ) use ( $tags ) {
            return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                $query->whereIntegerInRaw('tag_id', $tags);
            });
        });
        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    // Создать новый фрагмент. Функционал пользователя и администратора.
    public function store( CreateFragmentRequest $request ): \Illuminate\Http\JsonResponse {
        $user = Auth::user();
        DB::transaction(function () use ( $request, $user ) {
            if ( $request->input('type') == 'article' ) {
                $data = new Article(['content' => $request->input('content')]);
                $data->save();
            }
            elseif ( $request->input('type') == 'test' ) {
                $data = new Test(['content' => $request->input('content')]);
                $data->save();
            }
            elseif ( $request->input('type') == 'video' ) {
                $data = new Video();
                $data->content = '1';
                $data->save();
                $data->addMediaFromRequest('content')->toMediaCollection('fragments_videos', 'fragments');
                $data->content = $data->getFirstMediaUrl('fragments_videos');
                $data->save();
            }
            elseif ( $request->input('type') == 'image' ) {
                $data = new Image();
                $data->annotation = $request->input('annotation');
                $data->content = '1';
                $data->save();
                $data->addMediaFromRequest('content')->toMediaCollection('fragments_images', 'fragments');
                $data->content = $data->getFirstMediaUrl('fragments_images');
                $data->save();
            }
            elseif ( $request->input('type') == 'game' ) {
                $data = new Game();
                $data->type = $request->input('game_type');
                $content = [];
                $data->content = json_encode($content);
                $data->save();
                $data->addMultipleMediaFromRequest(['content'])->each(function ( $file_adder ) use ( $user ) {
                    $file_adder->toMediaCollection('fragments_games', 'fragments');
                });
                $data_images = $data->getMedia('fragments_games');
                foreach ( $data_images as $data_image ) {
                    $content[] = $data_image->getFullUrl();
                }
                $data->content = json_encode($content, JSON_UNESCAPED_SLASHES);
                $data->save();
            }
            $fragment = new Fragment(['title' => $request->input('title')]);
            $fragment->user()->associate(Auth::user());
            $fragment->fragmentgable()->associate($data);
            $fragment->save();
            $fragment->tags()->sync($request->input('tags'));
            if ( isset($request->fon) )
                $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
        });
        return response()->json([
            'message' => 'Новый фрагмент успешно загружен!',
        ], 201);
    }

    // Получить определенный фрагмент. Функционал пользователя и администратора.
    public function show( Fragment $fragment ): FragmentResource {
        return new FragmentResource($fragment->load('tags')->loadCount('tags'));
    }

    // Обновить содержимое фрагмента. Функционал пользователя и администратора.
    public function update( UpdateFragmentRequest $request, Fragment $fragment ): \Illuminate\Http\JsonResponse {
        DB::transaction(function () use ( $request, $fragment ) {
            if ( $tags = $request->input('tags') ) {
                $tags = array_unique($tags);
                $fragment->tags()->sync($tags);
            }
            else {
                DB::table('fragment_tag')->where('fragment_id', $fragment->id)->delete();
            }
            if ( $fragment->fragmentgable_type == 'video' || $fragment->fragmentgable_type == 'image' ) {
                if ( $request->input('title') )
                    $fragment->update(['title' => $request->input('title')]);
                if ( $request->hasFile('content') ) {
                    $fragment->fragmentgable->clearMediaCollection("fragments_{$fragment->fragmentgable_type}s");
                    $fragment->fragmentgable->addMediaFromRequest('content')
                                            ->toMediaCollection("fragments_{$fragment->fragmentgable_type}s", 'fragments');
                    $fragment->fragmentgable->update(['content' => $fragment->fragmentgable->getFirstMediaUrl("fragments_{$fragment->fragmentgable_type}s")]);
                }

                if ( $fragment->fragmentgable_type == 'image' ) {
                    $fragment->fragmentgable->update(['annotation' => $request->input('annotation')]);
                }
            }
            else {
                if ( $fragment->fragmentgable_type == 'article' )
                    $request->validate(['content' => 'string'], ['string' => 'На вход ожидалась строка']);
                if ( $fragment->fragmentgable_type == 'test' )
                    $request->validate(['content' => 'json'], ['json' => 'На вход ожидались данные в формате JSON']);
                $fragment->update($request->only('title'));
                $fragment->fragmentgable->update($request->only('content'));
            }
            if ( $request->hasFile('fon') ) {
                if ( empty($fragment->getFirstMediaUrl('fragments_fons')) )
                    $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
                else {
                    $fragment->clearMediaCollection('fragments_fons');
                    $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
                }
            }
        });
        return response()->json([
            'message' => 'Фрагмент успешно обновлен',
        ], 200);

        /*        return response()->json([
                    'message' => 'Произошла ошибка',
                ], 400);*/
    }


    // Удалить фрагмент. Функционал пользователя и администратора. Мягкое удаление.
    public function destroy( Fragment $fragment ): \Illuminate\Http\JsonResponse {

        if ( $fragment->loadCount('lessons')->lessons_count > 0 ) {
            $lessons = $fragment->lessons()->whereHas('user', function ( Builder $query ) {
                $query->where('role', '<>', 'student');
            })->orderBy('title')->get(['id', 'title']);

            /*if ( Auth::user()->role == 'admin' ) {
                return response()->json([
                    'message'               => "Невозможно удалить фрагмент",
                    'all_lessons_count'     => $fragment->lessons_count,
                    'teacher_lessons_count' => $lessons->count(),
                    'lessons'               => $lessons,
                ], 400);
            }*/
            return response()->json([
                'message'               => "Не удалось удалить фрагмент",
                "all_lessons_count"     => $fragment->lessons_count,
                "teacher_lessons_count" => $lessons->count(),
                'lessons'               => $lessons,
            ], 400);
        }
        if ( $fragment->delete() ) {
            return response()->json([
                'message' => 'Фрагмент успешно удалён!',
            ], 200);
        }

        return response()->json([
            'message' => 'Не удалось удалить фрагмент',
        ], 400);
    }

    // Добавить/удалить фрагмент из избранного. Функционал пользователя и администратора.
    public function like( Request $request, Fragment $fragment ) {
        $query = DB::table('fragment_user')->where('fragment_id', $fragment->id)->where('user_id', Auth::id());
        if ( $query->exists() )
            $query->delete();
        else
            Auth::user()->favouriteFragments()->attach($fragment->id);
        return response(['message' => 'Ok'], 200);
    }

    // Получить список избранных фрагментов. Функционал пользователя и администратора.
    public function likeIndex( Request $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $fragments = Auth::user()->favouriteFragments()->withCount('tags')->with('tags')
                         ->when($title, function ( $query ) use ( $title ) {
                             return $query->where('title', 'ILIKE', '%' . $title . '%');
                         })->when($type, function ( $query ) use ( $type ) {
                return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
            })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            });

        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    // Получить список фрагментов текущего учителя.
    public function fragmentsTeacherIndex( Request $request, User $user ): FragmentResourceCollection {
        return new FragmentResourceCollection($user->fragments()->with('tags')->paginate(6));
    }
}
