<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class GameType extends Model
{
    protected $table = 'game_types';
    protected $fillable = [
        'title',
        'description',
        'task',
    ];

    /**
     * Take reverse one-to-many relation to Game
     * Обратная связь один-ко-многим с моделью Игра
     * @return HasMany
     */
    public function games(): HasMany {
        return $this->hasMany(Game::class);
    }

    /**
     * Get titles of games' types
     * Получить названия типов игр
     * @return Collection
     */
    public static function getTitlesTypes(): Collection {
        return GameType::pluck('type');
    }
}
