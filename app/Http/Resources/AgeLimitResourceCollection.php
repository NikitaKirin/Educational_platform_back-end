<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\AgeLimit */
class AgeLimitResourceCollection extends ResourceCollection
{
    public static $wrap = 'ageLimits';

    /**
     * @param Request $request
     * @return array
     */
    public function toArray( $request ) {
        return [
            'data' => $this->collection,
        ];
    }
}
