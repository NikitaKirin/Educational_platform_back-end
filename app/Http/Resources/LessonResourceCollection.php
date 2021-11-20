<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @see \App\Models\Lesson */
class LessonResourceCollection extends ResourceCollection
{
    public static $wrap = 'lessons';

    public function toArray( $request ): array {
        return [
            'all_count' => Auth::user()->favouriteLessons()->count(),
            'data'      => $this->collection,
        ];
    }
}
