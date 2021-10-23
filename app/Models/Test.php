<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Test extends Model
{
    protected $table = 'tests';

    protected $fillable = [
        'content',
    ];

    // Устанавливаем полиморфную связь "один с одним" с таблицей "fragments"
    public function fragment(): MorphOne {
        return $this->morphOne(Fragment::class, 'fragmentgable');
    }
}
