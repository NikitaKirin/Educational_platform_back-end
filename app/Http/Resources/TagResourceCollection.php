<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

/** @see \App\Models\Tag */
class TagResourceCollection extends ResourceCollection
{
    public static $wrap = 'tags';
    public function toArray( $request ): array {
        return [
            'data' => $this->collection,
        ];
    }
}
