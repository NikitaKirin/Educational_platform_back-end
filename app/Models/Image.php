<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Image extends Model implements HasMedia
{

    use InteractsWithMedia;

    protected $table = 'images';
    public $timestamps = false;
    protected $fillable = [
        'content',
    ];

    // Устанавливаем полиморфную связь "один с одним" с таблицей "fragments"
    public function fragment(): \Illuminate\Database\Eloquent\Relations\MorphOne {
        return $this->morphOne(Fragment::class, 'fragmentgable');
    }
}
