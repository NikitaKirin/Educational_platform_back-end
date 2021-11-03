<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Fragment\UpdateFragmentRequest;
use App\Http\Resources\FragmentResource;
use App\Http\Resources\FragmentResourceCollection;
use App\Models\Article;
use App\Models\Test;
use App\Models\Fragment;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FragmentController extends Controller
{
    // Вывести список фрагментов текущего пользователя.
    public function myIndex( Request $request ) {
        $request->validate([
            'title' => ['nullable', 'string'],
            'type'  => ['nullable', 'string', 'in:article,test,video'],
        ], [
            'string' => 'Введены некорректные символы',
            'in'     => 'Выбран несуществующий тип фрагмента. Доступны следующие значения: :values',
        ]);

        if ( $title = $request->input('title') ) {
            if ( $type = $request->input('type') ) {
                $query = Fragment::where('title', 'ILIKE', '%' . $title . '%')->where('fragmentgable_type', $type)
                                 ->where('user_id', '=', Auth::user()->id)->orderBy('title')->paginate(6);
            }
            else {
                $query = Fragment::where('title', 'ILIKE', '%' . $title . '%')->where('user_id', '=', Auth::user()->id)
                                 ->orderBy('title')->paginate(6);
            }
            return new FragmentResourceCollection($query);
        }
        elseif ( $type = $request->input('type') ) {
            return new FragmentResourceCollection(Fragment::where('fragmentgable_type', $type)
                                                          ->where('user_id', '=', Auth::user()->id)->orderBy('title')
                                                          ->paginate(6));
        }
        return new FragmentResourceCollection(Fragment::where('user_id', '=', Auth::user()->id)->orderBy('title', 'asc')
                                                      ->paginate(6));
    }

    // Вывести список всех фрагментов. Функционал пользователя и администратора.
    public function index( Request $request ): FragmentResourceCollection {
        $request->validate([
            'title' => ['nullable', 'string'],
            'type'  => ['nullable', 'string', 'in:article,test,video'],
        ], [
            'string' => 'Введены некорректные символы',
            'in'     => 'Выбран несуществующий тип фрагмента. Доступны следующие значения: :values',
        ]);
        if ( $title = $request->input('title') ) {
            if ( $type = $request->input('type') ) {
                $query = Fragment::where('title', 'ILIKE', '%' . $title . '%')->where('fragmentgable_type', $type)
                                 ->orderBy('title')->paginate(6);
            }
            else {
                $query = Fragment::where('title', 'ILIKE', '%' . $title . '%')->orderBy('title')->paginate(6);
            }
            return new FragmentResourceCollection($query);
        }
        elseif ( $type = $request->input('type') ) {
            return new FragmentResourceCollection(Fragment::where('fragmentgable_type', $type)->orderBy('title')
                                                          ->paginate(6));
        }
        return new FragmentResourceCollection(Fragment::orderBy('title', 'asc')->paginate(6));
    }

    // Создать новый фрагмент. Функционал пользователя и администратора.
    public function store( CreateFragmentRequest $request ) {
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
        return new FragmentResource($fragment);
    }

    // Обновить содержимое фрагмента. Функционал пользователя и администратора.
    public function update( UpdateFragmentRequest $request, Fragment $fragment ) {
        if ( $fragment->fragmentgable_type == 'video' ) {
            $fragment->update(['title' => $request->input('title')]);
            $fragment->fragmentgable->clearMediaCollection('fragments_videos');
            $fragment->fragmentgable->addMediaFromRequest('content')
                                    ->toMediaCollection('fragments_videos', 'fragments');
            $fragment->fragmentgable->refresh();
            $fragment->fragmentgable->content = $fragment->fragmentgable->getFirstMediaUrl('fragments_videos');
            $fragment->fragmentgable->save();
            return response()->json([
                'message' => 'Фрагмент успешно обновлен',
            ], 200);
        }
        else {
            if ( $fragment->fragmentgable_type == 'article' )
                $request->validate(['content' => 'string'], ['string' => 'На вход ожидалась строка']);
            if ( $fragment->fragmentgable_type == 'test' )
                $request->validate(['content' => 'json'], ['json' => 'На вход ожидались данные в формате JSON']);
            $fragment->update(['title' => $request->input('title')]);
            $fragment->fragmentgable->update(['content' => $request->input('content')]);
            return response()->json([
                'message' => 'Фрагмент успешно обновлен',
            ], 200);
        }

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
}
