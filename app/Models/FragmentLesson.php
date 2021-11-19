<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class FragmentLesson extends Pivot
{
    use SoftDeletes;

    protected $table = 'lesson_fragment';

    public function fragments(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Fragment::class);
    }

    public function lessons(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Lesson::class);
    }
}
