<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FragmentLesson extends Pivot
{
    protected $table = 'lesson_fragment';

    public function fragments(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Fragment::class);
    }

    public function lessons(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Lesson::class);
    }
}
