<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

/** @see \App\Models\Fragment */
class FragmentResourceCollection extends ResourceCollection
{
    public static $wrap = 'fragments';
    public function toArray( $request ): array {
        return [
            'fragments' => $this->collection,
        ];
    }
}
