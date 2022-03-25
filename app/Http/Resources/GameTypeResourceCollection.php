<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\GameType */
class GameTypeResourceCollection extends ResourceCollection
{
    public static $wrap = 'gameTypes';
    /**
     * @param Request $request
     * @return array
     */
    public function toArray( $request ) {
        return [
            'gameTypes' => $this->collection,
        ];
    }
}
