<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Fragment\IndexFragmentRequest;
use App\Http\Requests\Api\Fragment\UpdateFragmentRequest;
use App\Http\Resources\FragmentResource;
use App\Http\Resources\FragmentResourceCollection;
use App\Models\Article;
use App\Models\Tag;
use App\Models\Test;
use App\Models\Fragment;
use App\Models\Video;
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
            });
        /*if ( $title = $request->input('title') ) {
            if ( $type = $request->input('type') ) {
                $query = Fragment::with('tags')->withCount('tags')->where('title', 'ILIKE', '%' . $title . '%')
                                 ->where('fragmentgable_type', $type)->where('user_id', '=', Auth::user()->id)
                                 ->orderBy('title')->paginate(6);
            }
            else {
                $query = Fragment::with('tags')->withCount('tags')->where('title', 'ILIKE', '%' . $title . '%')
                                 ->where('user_id', '=', Auth::user()->id)->orderBy('title')->paginate(6);
            }
            return new FragmentResourceCollection($query);
        }
        elseif ( $type = $request->input('type') ) {
            return new FragmentResourceCollection(Fragment::with('tags')->withCount('tags')
                                                          ->where('fragmentgable_type', $type)
                                                          ->where('user_id', '=', Auth::user()->id)->orderBy('title')
                                                          ->paginate(6));
        }*/

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
        });
        /*if ( $title = $request->input('title') ) {
            if ( $type = $request->input('type') ) {
                $query = Fragment::with('tags')->withCount('tags')->where('title', 'ILIKE', '%' . $title . '%')
                                 ->where('fragmentgable_type', $type)->orderBy('title')->paginate(6);
            }
            else {
                $query = Fragment::with('tags')->withCount('tags')->where('title', 'ILIKE', '%' . $title . '%')
                                 ->orderBy('title')->paginate(6);
            }
            return new FragmentResourceCollection($query);
        }
        elseif ( $type = $request->input('type') ) {
            return new FragmentResourceCollection(Fragment::with('tags')->withCount('tags')
                                                          ->where('fragmentgable_type', $type)->orderBy('title')
                                                          ->paginate(6));
        }*/
        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    // Создать новый фрагмент. Функционал пользователя и администратора.
    public function store( CreateFragmentRequest $request ): \Illuminate\Http\JsonResponse {
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
        $fragment = new Fragment(['title' => $request->input('title')]);
        $fragment->user()->associate(Auth::user());
        $fragment->fragmentgable()->associate($data);
        if ( $fragment->save() ) {
            $fragment->tags()->sync($request->input('tags'));
            return response()->json([
                'message' => 'Новый фрагмент успешно загружен!',
            ], 201);
        }
        return response()->json([
            'message' => 'Произошла ошибка при создании фрагмента',
        ], 400);
    }

    // Получить определенный фрагмент. Функционал пользователя и администратора.
    public function show( Fragment $fragment ): FragmentResource {
        return new FragmentResource($fragment->load('tags')->loadCount('tags'));
    }

    // Обновить содержимое фрагмента. Функционал пользователя и администратора.
    public function update( UpdateFragmentRequest $request, Fragment $fragment ): \Illuminate\Http\JsonResponse {
        if ( $tags = $request->input('tags') ) {
            $tags = array_unique($tags);
            $fragment->tags()->sync($tags);
        }
        else {
            DB::table('fragment_tag')->where('fragment_id', $fragment->id)->delete();
        }
        if ( $fragment->fragmentgable_type == 'video' ) {
            $fragment->update(['title' => $request->input('title')]);
        }
        else {
            if ( $fragment->fragmentgable_type == 'article' )
                $request->validate(['content' => 'string'], ['string' => 'На вход ожидалась строка']);
            if ( $fragment->fragmentgable_type == 'test' )
                $request->validate(['content' => 'json'], ['json' => 'На вход ожидались данные в формате JSON']);
            $fragment->update($request->only('title'));
            $fragment->fragmentgable->update($request->only('content'));
        }
        return response()->json([
            'message' => 'Фрагмент успешно обновлен',
        ], 200);

        /*        return response()->json([
                    'message' => 'Произошла ошибка',
                ], 400);*/
    }


    // Удалить фрагмент. Функционал пользователя и администратора. Мягкое удаление.
    public function destroy( Fragment $fragment ): \Illuminate\Http\JsonResponse {
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
}
