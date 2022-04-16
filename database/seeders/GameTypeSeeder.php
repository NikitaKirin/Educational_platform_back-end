<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameType;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    public function run() {
        $gameType = new GameType(['type' => 'pairs', 'title' => 'Парочки', 'description' => 'Какое-то описание']);
        $gameType->save();
        $gameTypeSecond = new GameType([
            'type'        => 'matchmaking',
            'title'       => 'Ассоциации',
            'description' => 'Какое-то описание',
        ]);
        $gameTypeSecond->save();
        $gameTypeThird = new GameType([
            'type'        => 'sequences',
            'title'       => 'Последовательности',
            'description' => 'Какое-то описание',
        ]);
        $gameTypeThird->save();
    }
}
