<?php

namespace Database\Seeders;

use App\Models\AgeLimit;
use App\Models\Article;
use App\Models\Test;
use App\Models\Fragment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FragmentSeeder extends Seeder
{
    public function run() {
        for ( $i = 0; $i <= 200; $i++ ) {
            $user = User::all()->pluck('id');
            $ageLimit = AgeLimit::all()->pluck('id');
            $article = new Article(['content' => '<p>' . Str::random(450) . '</p>']);
            $article->save();
            $fragment = new Fragment(['title' => Str::random(10)]);
            $fragment->user()->associate($user->random());
            $fragment->ageLimit()->associate(AgeLimit::find($ageLimit->random()));
            $fragment->fragmentgable()->associate($article);
            $fragment->save();
        }
        /*for ( $i = 0; $i <= 200; $i++ ) {
            $user = User::find(rand(1, 100));
            $test = new Test([
                'content' => json_encode([
                    "title" => "Example-test",
                    "main"  => [
                        "Question_1" => [
                            "question" => "title_question",
                            "type"     => "one",
                            "answers"  => [
                                "answer_1" => "true",
                                "answer_2" => "false",
                                "answer_3" => "false",
                            ],
                        ],
                        "Question_2" => [
                            "question" => "title_question",
                            "type"     => "many",
                            "answers"  => [
                                "answer_1" => "true",
                                "answer_2" => "true",
                                "answer_3" => "false",
                            ],
                        ],
                        "Question_3" => [
                            "question" => "title_question",
                            "type"     => "word",
                            "answers"  => [
                                "answer_1" => "true",
                                "answer_2" => "false",
                                "answer_3" => "false",
                            ],
                        ],
                    ],
                ]),
            ]);
            $test->save();
            $fragment = new Fragment(['title' => Str::random(10)]);
            $fragment->user()->associate($user);
            $fragment->fragmentgable()->associate($test);
            $fragment->save();
        }*/
    }
}
