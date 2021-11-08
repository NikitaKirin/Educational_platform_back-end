<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Http\Resources\TagResourceCollection;
use App\Models\Tag;
use Illuminate\Http\Request;

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

    public function destroy( $id ) {
        //
    }
}
