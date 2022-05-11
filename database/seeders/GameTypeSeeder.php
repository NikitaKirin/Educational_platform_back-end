<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameType;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    public function run() {
        $gameType = new GameType([
            'type'        => 'pairs',
            'title'       => 'Парочки',
            'description' => 'Развивающая игра, где необходимо запомнить расположение одинаковых карточек. После начала игры карточки переворачиваются, а игроку надо находить пары изображений.',
            'task'        => 'Найди одинаковые картинки.',
        ]);
        $gameType->save();
        $gameTypeSecond = new GameType([
            'type'        => 'matchmaking',
            'title'       => 'Ассоциации',
            'description' => 'Развивающая игра, в которой необходимо соединять изображения по заранее заданному признаку.',
            'task'        => 'Соедини картинки по признаку, как показано в примере.',
        ]);
        $gameTypeSecond->save();
        $gameTypeThird = new GameType([
            'type'        => 'sequences',
            'title'       => 'Последовательности',
            'description' => 'Развивающая игра, в которой необходимо расположить картинки в заранее заданном порядке.',
            'task'        => 'Расположи картинки в правильном порядке.',
        ]);
        $gameTypeThird->save();
        $gameTypeFourth = new GameType([
            'type'        => 'puzzles',
            'title'       => 'Пазлы',
            'description' => 'Развивающая игра, суть которой состоит в соединении кусочков изображения единую картинку по образцу.',
            'task'        => 'Собери картинку.',
        ]);
        $gameTypeFourth->save();
    }
}
