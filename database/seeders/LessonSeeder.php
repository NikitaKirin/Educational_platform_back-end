<?php

namespace Database\Seeders;

use App\Models\AgeLimit;
use App\Models\Fragment;
use App\Models\Lesson;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    public function run() {
        $users = User::where('role', 'creator')->pluck('id');
        $fragments = Fragment::all()->pluck('id');
        $tags = Tag::all()->pluck('id');
        $ageLimits = AgeLimit::all()->pluck('id');
        for ( $i = 0; $i < 10; $i++ ) {
            $lesson = new Lesson([
                'title'      => Str::random(10),
                'annotation' => Str::random(15),
                'user_id'    => $users->random(),
            ]);
            $lesson->ageLimit()->associate($ageLimits->random());
            $lesson->save();
            $rand = rand(2, 8);
            for ( $j = 0; $j < $rand; $j++ ) {
                $lesson->fragments()->attach($fragments->random(), ['order' => $j]);
            }
            $lesson->tags()->sync($tags->random(rand(2, 10)));
            $lesson->save();
        }
    }
}
