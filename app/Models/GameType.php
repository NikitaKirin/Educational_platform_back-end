<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

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

    /**
     * Get titles of games' types
     * Получить названия типов игр
     * @return Collection
     */
    public static function getTitlesTypes(): Collection {
        return GameType::pluck('title');
    }
}
