<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

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
            'fragments_count' => $this->fragments_count,
            'tags_count'      => $this->when($this->tags()->exists(), $this->tags()->count()),
            'tags'            => $this->when($this->tags()
                                                  ->exists(), new TagResourceCollection($this->whenLoaded('tags'))),
        ];
    }
}
