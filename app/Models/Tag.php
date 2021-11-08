<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'value',
    ];

    // Устанавливаем связь "многие со многим" с таблицей "fragments" через промежуточную таблицу "fragment_tag"
    public function fragments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Fragment::class);
    }
}
