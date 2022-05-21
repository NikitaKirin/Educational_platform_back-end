<?php

namespace App\Orchid\Screens\GameType;

use App\Models\GameType;
use App\Orchid\Layouts\GameType\GameTypeEditLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class GameTypeEditScreen extends Screen
{
    public GameType $gameType;

    /**
     * Query data.
     *
     * @return array
     */
    public function query( GameType $gameType ): iterable {
        return [
            'gameType' => $gameType,
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return __('Изменить данные типа игры');
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
            Layout::block([GameTypeEditLayout::class,])
                  ->title(__('Информация о типе игры'))
                  ->description(__('Введите новую информацию о типе игры'))
                  ->commands(
                      Button::make(__('Save'))
                            ->type(Color::DEFAULT())
                            ->icon('check')
                            ->method('save')
                  ),
        ];
    }

    public function save( Request $request, GameType $gameType ) {
        $request->validate([
            'gameType.title'       => ['required', 'string'],
            'gameType.description' => ['required', 'string', 'max:1000'],
            'gameType.task'        => ['required', 'string'],
        ],
            [
                'gameType.required' => 'Поле обязательно для заполнения',
                'gameType.string'   => 'На вход ожидалась строка',
            ]);
        $gameType->fill($request->get('gameType'));
        $gameType->save();
        Toast::success('Тип игры успешно сохранен');
        return redirect()->route('platform.systems.gameTypes');
    }
}
