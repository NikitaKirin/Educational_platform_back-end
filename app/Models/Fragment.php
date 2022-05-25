<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Fragment extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Filterable, AsSource;

    protected $table = 'fragments';

    protected $fillable = [
        'title',
    ];

    // Устанавливаем обратную связь "один ко многим" с таблицей 'users'
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // Устанавливаем обратную связь "один с одним" с первичными таблицами: "articles", "tests", "videos", "images",
    // "games"
    public function fragmentgable(): MorphTo {
        return $this->morphTo();
    }

    // Устанавливаем связь "многие со многим" с таблицей "tags" через связующую таблицу "fragment_tag"
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Tag::class);
    }

    // Устанавливаем связь "многие со многим" с таблицей "users" через связующую таблицу "fragment_user"
    // реализуем функцию добавить фрагмент в избранное
    public function favouritableUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(User::class, 'fragment_table');
    }

    // Устанавливаем связь "многие со многим" с таблицей "lessons" через связующую таблицу "fragment_lesson"
    public function lessons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Lesson::class)->withPivot('order');
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
