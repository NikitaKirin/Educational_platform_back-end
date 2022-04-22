<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeLimit extends Model
{
    use HasFactory;

    protected $table = 'age_limits';

    protected $fillable = [
        'number_context',
        'text_context',
    ];


    /**
     * Set the relation with model Fragment
     * Устанавливает связь с моделью Fragment
     * @return HasMany
     */
    public function fragments(): HasMany {
        return $this->hasMany(Fragment::class);
    }
}
