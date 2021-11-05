<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run() {
        $tags = ['природа', 'азбука', 'грамматика', 'математика', 'спорт', 'животные', 'растения'];

        foreach ( $tags as $tag ) {
            $ex = new Tag(['value' => $tag]);
            $ex->save();
        }
    }
}
