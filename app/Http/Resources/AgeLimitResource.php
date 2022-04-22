<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AgeLimit */
class AgeLimitResource extends JsonResource
{
    public static $wrap = 'ageLimit';
    /**
     * @param Request $request
     * @return array
     */
    public function toArray( $request ) {
        return [
            'id'              => $this->id,
            'number_context'  => $this->number_context,
            'text_context'    => $this->text_context,
            //'fragments_count' => $this->fragments_count,
            //'fragments' => FragmentResourceCollection::collection($this->whenLoaded('fragments')),
        ];
    }
}
