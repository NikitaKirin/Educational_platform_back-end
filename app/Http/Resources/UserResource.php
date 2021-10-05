<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = 'users';

    public function toArray( $request ): array {

        if ( $this->birthday != null ) {
            return [
                'id'       => $this->id,
                'name'     => $this->name,
                'birthday' => Carbon::parse($this->birthday)->format('d.m.Y'),
                'role'     => $this->role,
                'email'    => $this->email,
            ];
        }
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'birthday' => null,
            'role'     => $this->role,
            'email'    => $this->email,
        ];
    }
}
