<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected $casts = [
        'content' => 'array',
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
     * @return BelongsTo
     */
    public function gameType(): BelongsTo {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    /**
     * Mutator for content attribute
     * Мутатор
     * @param $value
     */
    public function setContentAttribute( $value ) {
        if ( isset($value) ) {
            $this->attributes['content'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
}
