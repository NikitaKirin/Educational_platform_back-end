<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @see \App\Models\Lesson */
class LessonResourceCollection extends ResourceCollection
{
    public static $wrap = 'lessons';

    public function toArray( $request ): array {
        return [
            'all_count' => $this->when(true, function () use ( $request ) {
                if ( $request->is('api/lessons/like*') )
                    return Auth::user()->favouriteLessons()->count();
                return Lesson::all()->count();
            }),
            'data'      => $this->collection,
        ];
    }
}
