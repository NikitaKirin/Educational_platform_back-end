<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = 'user';

    public function toArray( $request ): array {

        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'birthday' => $this->birthday,
            'role'     => $this->role,
            'email'    => $this->email,
            'avatar'   => $this->getAvatar(),
        ];
    }
}
