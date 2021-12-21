<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;
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
                elseif ( $request->routeIs('lesson.index.my') )
                    return Auth::user()->lessons()->count();
                elseif ( $request->routeIs('lesson.teacher.index') )
                    return $this->when($request->user->lessons()->exists(), $request->user->lessons()->count(), 0);
                return Lesson::whereHas('user', function ( Builder $query ) {
                    $query->where('role', '=', 'creator');
                })->count();
            }),
            'data'      => $this->collection,
        ];
    }
}
