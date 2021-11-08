<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'value',
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
    public function fragments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Fragment::class);
    }
}
