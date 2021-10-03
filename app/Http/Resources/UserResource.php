<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = 'users';

    public function toArray( $request ) {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'email' => $this->email
        ];
    }
}
