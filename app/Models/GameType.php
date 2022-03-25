<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    protected $table = 'game_types';
    protected $fillable = [
        'title',
        'description',
    ];
}
