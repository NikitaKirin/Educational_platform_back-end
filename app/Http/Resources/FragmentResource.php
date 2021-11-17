<?php

namespace App\Http\Resources;

use App\Models\Fragment;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** @mixin Fragment */
class FragmentResource extends JsonResource
{
    public static $wrap = 'fragment';

    public function toArray( $request ): array {
        return [
            'id'         => $this->id,
            'type'       => $this->fragmentgable_type,
            'title'      => $this->title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_name'  => $this->user->name,
            'user_id'    => $this->user_id,
            'content'    => $this->fragmentgable->content,
            'favourite'  => $this->when(Auth::user()->favouriteFragments()->where('fragment_id', $this->id)
                                            ->exists(), true, false),

            'tags_count' => $this->when($this->tags()->exists(), $this->tags()->count()),
            'tags'       => $this->when($this->tags()->exists(), new TagResourceCollection($this->whenLoaded('tags'))),
        ];
    }
}
