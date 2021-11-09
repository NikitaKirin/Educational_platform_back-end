<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Http\Resources\TagResourceCollection;
use App\Models\Fragment;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    // Вывести список всех тегов. Функционал администратора и пользователя.
    public function index(): TagResourceCollection {
        return new TagResourceCollection(Tag::all()->loadCount('fragments'));
    }

    public function store( Request $request ) {
        //
    }

    public function show( Tag $tag ) {
        return new TagResource($tag->loadCount('fragments'));
    }

    public function update( Request $request, $id ) {
        //
    }

    // Удалить тег у определенного фрагмента. Функционал администратора и пользователя.
    public function destroy( Tag $tag, Fragment $fragment ) {
        if ( DB::table('fragment_tag')->where('fragment_id', $fragment->id)->where('tag_id', $tag->id)->delete() )
            return response(['message' => 'Тег успешно удалён!']);
        return response(['message' => 'Произошла ошибка при удалении'], 400);
    }
}
