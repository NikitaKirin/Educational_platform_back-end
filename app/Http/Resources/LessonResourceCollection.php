<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

/** @see \App\Models\Lesson */
class LessonResourceCollection extends ResourceCollection
{
    public static $wrap = 'lessons';

    public function toArray( $request ): array {
        return [
            'data' => $this->collection,
        ];
    }
}
