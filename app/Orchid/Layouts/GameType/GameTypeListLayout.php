<?php

namespace App\Orchid\Layouts\GameType;

use App\Models\Game;
use App\Models\GameType;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class GameTypeListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'gameTypes';

    protected function striped(): bool {
        return true;
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable {
        return [
            TD::make('#')
              ->render(function ( GameType $gameType, object $loop ) {
                  return $loop->index+1;
              }),
            TD::make('type', __('Тип'))
              ->defaultHidden(true),
            TD::make('title', __('Название'))
              ->sort()
              ->filter(),
            TD::make('description', __('Описание'))->width('300px'),
            TD::make('task', __('Задание по умолчанию'))->width('150px'),
            TD::make('updated_at', __('Последние изменения'))
              ->sort()
              ->render(function ( GameType $gameType ) {
                  return $gameType->updated_at->toDateTimeString();
              }),
            TD::make(__('Actions'))
              ->render(function ( GameType $gameType ) {
                  return DropDown::make()
                                 ->icon('options-vertical')
                                 ->list([
                                     Link::make(__('Edit'))
                                         ->icon('pencil')
                                         ->route('platform.systems.gameTypes.edit', ['gameType' => $gameType->id]),
                                     Button::make(__('Delete'))
                                           ->icon('trash')
                                           ->method('remove', ['gameType' => $gameType->id]),
                                 ]);
              }),
        ];
    }
}
