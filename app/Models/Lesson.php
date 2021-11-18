<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'title',
        'annotation',
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
        return $this->belongsToMany(Fragment::class);
    }
}
