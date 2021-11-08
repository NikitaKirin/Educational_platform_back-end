<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/** @mixin \App\Models\Tag */
class TagResource extends JsonResource
{
    public static $wrap = 'tag';

    public function toArray( $request ): array {
        return [
            'id'              => $this->id,
            'value'           => $this->value,
            'fragments_count' => $this->when($this->fragments()->exists(), $this->fragments()->count()),

            'fragments' => new FragmentResourceCollection($this->whenLoaded('fragments')),
        ];
    }
}
