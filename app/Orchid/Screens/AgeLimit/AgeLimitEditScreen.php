<?php

namespace App\Orchid\Screens\AgeLimit;

use App\Models\AgeLimit;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class AgeLimitEditScreen extends Screen
{
    public AgeLimit $ageLimit;

    /**
     * Query data.
     *
     * @return array
     */
    public function query( AgeLimit $ageLimit ): iterable {
        return [
            'ageLimit' => $ageLimit,
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return $this->ageLimit->exists ? __('Изменить возрастной ценз') : __('Создать возрастной ценз');
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable {
        return [
            Button::make(__('Remove'))
                  ->icon('trash')
                  ->method('remove')
                  ->canSee($this->ageLimit->exists),

            Button::make(__('Save'))
                  ->icon('check')
                  ->method('save'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable {
        return [
            Layout::rows([
                Input::make(__('ageLimit.number_context'))
                     ->title(__('Числовой контекст'))
                     ->type('number')
                     ->max(255)
                     ->required(),
                Input::make(__('ageLimit.text_context'))
                     ->title(__('Текстовой контекст'))
                     ->type('text')
                     ->max(255)
                     ->required(),
            ]),
        ];
    }

    public function save( Request $request, AgeLimit $ageLimit ) {
        $request->validate([
            'ageLimit.number_context' => ['required', 'numeric'],
            'ageLimit.text_context'   => ['required', 'string'],
        ],
            [
                'ageLimit.required' => 'Это поле обязательно для заполнения',
                'ageLimit.numeric'  => 'Ожидалось число',
                'ageLimit.string'   => 'Ожидался текст',
            ]);

        $ageLimit->fill([
            'number_context' => $request->input('ageLimit.number_context'),
            'text_context'   => $request->input('ageLimit.text_context'),
        ]);
        $ageLimit->save();
        Toast::success(__('Возрастной ценз успешно сохранён!'));

        return redirect()->route('platform.systems.ageLimits');
    }

    public function remove( AgeLimit $ageLimit ) {
        $ageLimit->delete();

        Toast::success(__('Возрастной ценз успешно удалён!'));
    }
}
