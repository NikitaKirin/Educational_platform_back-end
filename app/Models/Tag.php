<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Tag extends Model
{
    use AsSource, Filterable;

    protected $table = 'tags';

    protected $fillable = [
        'value',
    ];

    protected $allowedFilters = [
        'id',
        'value',
    ];
    protected $allowedSorts = [
        'id',
        'value',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Получить существующие id тегов
    public static function getValues() {
        $tags = Tag::all();
        $res = [];
        foreach ( $tags as $tag ) {
            array_push($res, $tag->id);
        }
        return $res;
    }

    // Устанавливаем связь "многие со многим" с таблицей "fragments" через промежуточную таблицу "fragment_tag"
    public function fragments(): BelongsToMany {
        return $this->belongsToMany(Fragment::class);
    }

    /**
     * Relation with Lesson
     * @return BelongsToMany
     */
    public function lessons(): BelongsToMany {
        return $this->belongsToMany(Lesson::class);
    }
}
