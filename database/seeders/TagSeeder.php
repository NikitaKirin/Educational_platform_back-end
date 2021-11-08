<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run() {
        $tags = [
            'развитие речи',
            'окружающий мир',
            'гигиена',
            'профессии',
            'этикет',
            'математика',
            'своими руками',
            'литература',
            'рисование',
            'физическая культура',
            'чтение',
            'музыка',
            'ОБЖ',
        ];

        foreach ( $tags as $tag ) {
            $ex = new Tag(['value' => $tag]);
            $ex->save();
        }
    }
}
