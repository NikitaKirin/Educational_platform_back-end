<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/** @mixin \App\Models\Tag */
class TagResource extends JsonResource
{
    public function toArray( $request ): array {
        return [
            'id'              => $this->id,
            'value'           => $this->value,
            'fragments_count' => $this->fragments_count,

            'fragments' => new FragmentResourceCollection($this->whenLoaded('fragments')),
        ];
    }
}
