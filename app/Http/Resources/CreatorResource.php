<?php

namespace App\Http\Resources;

use App\Models\Fragment;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/** @mixin \App\Models\User */
class CreatorResource extends JsonResource
{
    public static $wrap = 'creator';

    public function toArray( $request ): array {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'avatar'          => User::getAvatar(User::find($this->id)),
            'fragments_count' => $this->fragments_count,
            'lessons_count'   => $this->lessons_count
            //'fragments'       => new FragmentResourceCollection($this->fragments()->paginate(6)),
        ];
    }
}
