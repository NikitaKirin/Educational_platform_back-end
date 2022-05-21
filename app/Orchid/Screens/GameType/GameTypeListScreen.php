<?php

namespace App\Orchid\Screens\GameType;

use App\Models\GameType;
use App\Orchid\Layouts\GameType\GameTypeListLayout;
use Orchid\Screen\Screen;

class GameTypeListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable {
        return [
            'gameTypes' => GameType::filters()
                                   ->defaultSort('title')
                                   ->withCount(['games',])
                                   ->paginate(10),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return "Тип игры";
    }

    public function description(): ?string {
        return "Все типы игр, доступные на платформе";
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable {
        return [
            GameTypeListLayout::class,
        ];
    }
}
