<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Metrics\Chartable;
use Orchid\Screen\AsSource;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Lesson extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Filterable, AsSource, Chartable;

    protected $table = 'lessons';

    protected $fillable = [
        'id',
        'title',
        'annotation',
        'user_id',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id',
        'title',
        'annotation',
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'title',
        'age_limit_id',
        'updated_at',
        'created_at',
    ];

    // Устанавливаем обратную связь "один ко многим" с таблицей 'users'
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // Устанавливаем связь "многие со многим" с таблицей "tags" через связующую таблицу "lesson_tag"
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Tag::class);
    }

    // Устанавливаем связь "многие со многим" с таблицей "lesson_fragment" через связующую таблицу "fragment_tag"
    public function fragments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Fragment::class)->withPivot('order');
    }

    // Устанавливаем связь "многие со многим" с таблицей "users" через связующую таблицу "fragment_user"
    // реализуем функцию добавить фрагмент в избранное
    public function favouritableUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(User::class, 'fragment_table');
    }

    /**
     * Set relation with AgeLimit
     * Устанавливает связь с моделью AgeLimit
     * @return BelongsTo
     */
    public function ageLimit(): BelongsTo {
        return $this->belongsTo(AgeLimit::class, 'age_limit_id');
    }
}
