<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

/** @see \App\Models\User */
class UserResourceCollection extends ResourceCollection
{
    public static $wrap = 'users';
    public function toArray( $request ): array {
        return [
            'users' => $this->collection,
        ];
    }
}
