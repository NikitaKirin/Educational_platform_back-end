<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GameType extends Model
{
    protected $table = 'game_types';
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * Take reverse one-to-many relation to Game
     * Обратная связь один-ко-многим с моделью Игра
     * @return BelongsToMany
     */
    public function games(): BelongsToMany {
        return $this->belongsToMany(Game::class);
    }
}
