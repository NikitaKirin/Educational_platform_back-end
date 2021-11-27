<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @mixin \App\Models\Lesson */
class LessonResource extends JsonResource
{
    public static $wrap = 'lesson';

    public function toArray( $request ): array {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'annotation'      => $this->annotation,
            'user_id'         => $this->user_id,
            'user_name'       => $this->user->name,
            'user_avatar'     => User::getAvatar($this->user),
            'favourite'       => $this->when(Auth::user()->favouriteLessons()->where('lesson_id', $this->id)
                                                 ->exists(), true, false),
            'fragments_count' => $this->when($this->fragments()->exists(), $this->fragments()->count(), 0),
            'fragments'       => $this->whenLoaded('fragments', function () {
                return new FragmentResourceCollection($this->fragments->load('tags'));
            }),
            'tags_count'      => $this->when($this->tags()->exists(), $this->tags()->count()),
            'tags'            => $this->when($this->tags()
                                                  ->exists(), new TagResourceCollection($this->whenLoaded('tags'))),
        ];
    }
}
