<?php

namespace Database\Seeders;

use App\Models\GameType;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    public function run() {
        $gameType = new GameType(['title' => 'pairs', 'description' => 'Какое-то описание']);
        $gameType->save();
        $gameTypeSecond = new GameType(['title' => 'matchmaking', 'description' => 'Какое-то описание']);
        $gameTypeSecond->save();
    }
}
