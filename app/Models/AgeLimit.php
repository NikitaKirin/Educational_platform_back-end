<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class AgeLimit extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $table = 'age_limits';

    protected $fillable = [
        'number_context',
        'text_context',
    ];

    protected $allowedFilters = [
        'number_context',
        'text_context',
    ];
    protected $allowedSorts = [
        'number_context',
        'updated_at',
    ];


    /**
     * Set the relation with model Fragment
     * Устанавливает связь с моделью Fragment
     * @return HasMany
     */
    public function fragments(): HasMany {
        return $this->hasMany(Fragment::class);
    }

    /**
     * Set the relation with model Lesson
     * Устанавливает связь с моделью Lesson
     * @return HasMany
     */
    public function lessons(): HasMany {
        return $this->hasMany(Lesson::class);
    }
}
