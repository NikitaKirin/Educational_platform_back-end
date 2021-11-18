<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/** @mixin \App\Models\Lesson */
class LessonResource extends JsonResource
{
    public function toArray( $request ): array {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'annotation' => $this->annotation,

            'fragments_count' => $this->fragments_count,
        ];
    }
}
