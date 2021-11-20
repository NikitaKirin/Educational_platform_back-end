<?php

namespace Database\Seeders;

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
        for ( $i = 0; $i < 200; $i++ ) {
            $lesson = new Lesson([
                'title'      => Str::random(10),
                'annotation' => Str::random(15),
                'user_id'    => $users[rand(1, 40)],
            ]);
            $lesson->save();
            $fragments_count = rand(3, 15);
            $tags_count = rand(2, 10);
            for ( $j = 0; $j < $fragments_count; $j++ ) {
                $fragment = $fragments[rand(10, 400)];
                if ( $lesson->fragments()->where('id', $fragment)->exists() )
                    continue;
                $lesson->fragments()->attach($fragment, ['order' => $j + 1]);
            }
            $lesson->tags()->sync($tags->random($tags_count));
            $lesson->save();
        }
    }
}
