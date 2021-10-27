<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Fragment\UpdateFragmentRequest;
use App\Http\Resources\FragmentResource;
use App\Http\Resources\FragmentResourceCollection;
use App\Models\Article;
use App\Models\Fragment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FragmentController extends Controller
{
    // Вывести список всех фрагментов. Функционал пользователя и администратора.
    public function index( Request $request): FragmentResourceCollection {
        $request->validate([
            'title' => ['nullable', 'string'],
            'type'  => ['nullable', 'string', 'in:article,test,video'],
        ], [
            'string' => 'Введены некорректные символы',
            'in'     => 'Выбран несуществующий тип фрагмента',
        ]);
        if ( $title = $request->input('title') ) {
            if ( $type = $request->input('type') ) {
                $query = Fragment::where('title', 'LIKE', '%' . $title . '%')->where('fragmentgable_type', $type)
                                 ->orderBy('title')->paginate(6);
            }
            else {
                $query = Fragment::where('title', 'LIKE', '%' . $title . '%')->orderBy('title')->paginate(6);
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
            $request->validate(['content' => 'json'], ['json' => 'Введен неверный формат']);
            $article = new Article(['content' => $request->input('content')]);
            $article->save();
            $fragment = new Fragment(['title' => $request->input('title')]);
            $fragment->user()->associate(Auth::user());
            $fragment->fragmentgable()->associate($article);
            $fragment->save();
            return response()->json([
                'message' => 'Новый фрагмент успешно загружен!',
            ], 201);
        }
    }

    // Получить определенный фрагмент. Функционал пользователя и администратора.
    public function show( Fragment $fragment ): FragmentResource {
        return new FragmentResource($fragment);
    }

    // Обновить содержимое фрагмента. Функционал пользователя и администратора.
    public function update( UpdateFragmentRequest $request, Fragment $fragment ) {
        if ( $fragment->fragmentgable_type == 'article' ) {
            $request->validate(['content' => 'json'], ['json' => 'Введен неверный формат']);
            $fragment->update(['title' => $request->input('title')]);
            $fragment->fragmentgable->update(['content' => $request->input('content')]);
            return response()->json([
                'message' => 'Фрагмент успешно обновлен',
            ], 200);
        }

        return response()->json([
            'message' => 'Произошла ошибка',
        ], 400);
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