<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Game extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'games';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'content',
    ];

    /**
     * Take morphOne relation from Game to Fragment
     * Полиморфная связь один-с-один с таблицей фрагменты
     * @return MorphOne
     */
    public function fragment(): MorphOne {
        return $this->morphOne(Fragment::class, 'fragmentgable');
    }

    /**
     * Take one-to-many relation from Game to gameType
     * Связь один-со-многим с таблицей gameType
     * @return HasOne
     */
    public function gameType(): HasOne {
        return $this->hasOne(GameType::class);
    }
}