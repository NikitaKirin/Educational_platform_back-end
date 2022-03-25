<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GameType */
class GameTypeResource extends JsonResource
{
    public static $wrap = 'gameType';
    /**
     * @param Request $request
     * @return array
     */
    public function toArray( $request ) {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
        ];
    }
}
