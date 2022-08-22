<?php

namespace App\Orchid\Layouts\GameType;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class GameTypeEditLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable {
        return [
            Input::make('gameType.title')
                 ->type('text')
                 ->max(255)
                 ->title(__('Название'))
                 ->required(),
            TextArea::make('gameType.description')
                    ->rows(5)
                    ->title(__('Описание'))
                    ->required(),
            Input::make('gameType.task')
                 ->type('text')
                 ->max(255)
                 ->title(__('Задание'))
                 ->required(),
        ];
    }
}
