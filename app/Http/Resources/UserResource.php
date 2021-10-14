<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Self_;

class UserResource extends JsonResource
{
    public static $wrap = 'user';

    public function toArray( $request ): array {

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'birthday'   => $this->birthday,
            'role'       => $this->role,
            'email'      => $this->email,
            'avatar'     => User::getAvatar(User::find($this->id)),
            'blocked_at' => $this->blocked_at,
        ];
    }
}
