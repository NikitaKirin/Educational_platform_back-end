<?php

namespace Database\Seeders;

use App\Models\AgeLimit;
use Illuminate\Database\Seeder;

class AgeLimitSeeder extends Seeder
{
    private array $data = [
        '3+' => 3,
        '5+' => 5,
        '7+' => 7,
    ];

    public function run() {
        collect($this->data)->each(function ( $number_context, $text_context ) {
            $ageLimit = new AgeLimit(['number_context' => $number_context, 'text_context' => $text_context]);
            $ageLimit->save();
        });
    }
}
