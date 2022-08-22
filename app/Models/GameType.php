<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class GameType extends Model
{
    use AsSource, Filterable;

    protected $table = 'game_types';
    protected $fillable = [
        'title',
        'type',
        'description',
        'task',
    ];

    protected $allowedFilters = [
        'title',
    ];
    protected $allowedSorts = [
        'title',
        'updated_at',
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
