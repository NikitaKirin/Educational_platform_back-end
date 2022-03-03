<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Game extends Model
{
    protected $table = 'games';

    protected $fillable = [
        'type',
        'content',
    ];

    /**
     * Take morphOne relation from Game to Fragment
     * @return MorphOne
     */
    public function fragment(): MorphOne {
        return $this->morphOne(Fragment::class, 'fragmentgable');
    }
}
