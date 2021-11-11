<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Self_;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public static $wrap = 'user';

    public function toArray( $request ): array {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'birthday'        => $this->birthday,
            'role'            => $this->role,
            'email'           => $this->email,
            'avatar'          => User::getAvatar(User::find($this->id)),
            'blocked_at'      => $this->blocked_at,
            'fragments_count' => $this->when($this->fragments()->exists(), $this->fragments()->count()),
            'fragments'       => $this->when($this->fragments()
                                                  ->exists(), new FragmentResourceCollection($this->whenLoaded('fragments'))),
        ];
    }
}
