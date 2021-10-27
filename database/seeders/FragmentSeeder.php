<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Fragment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FragmentSeeder extends Seeder
{
    public function run() {
        for ( $i = 0; $i <= 200; $i++ ) {
            $user = User::find(rand(1, 100));
            $article = new Article(['content' => Str::random(450)]);
            $article->save();
            $fragment = new Fragment(['title' => Str::random(10)]);
            $fragment->user()->associate($user);
            $fragment->fragmentgable()->associate($article);
            $fragment->save();
        }
    }
}
