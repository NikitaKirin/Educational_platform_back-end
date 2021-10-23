<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Article extends Model
{
    protected $table = 'articles';
    public $timestamps = false;


    protected $fillable = [
        'content',
    ];

    // Устанавливаем полиморфную связь "один с одним" с таблицей "fragments"
    public function fragment(  ): MorphOne {
        return $this->morphOne(Fragment::class, 'fragmentgable');
    }

    // Устанавливаем
}
