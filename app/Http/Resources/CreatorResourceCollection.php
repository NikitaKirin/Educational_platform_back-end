<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

/** @see \App\Models\User */
class CreatorResourceCollection extends ResourceCollection
{
    public static $wrap = 'creators';

    public function toArray( $request ): array {
        return [
            'data' => $this->collection,
        ];
    }
}
