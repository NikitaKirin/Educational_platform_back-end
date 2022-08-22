<?php

namespace App\Orchid\Screens\AgeLimit;

use App\Models\AgeLimit;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class AgeLimitListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable {
        return [
            'ageLimits' => AgeLimit::filters()
                                   ->defaultSort('updated_at')
                                   ->withCount(['fragments', 'lessons'])
                                   ->paginate(),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return 'Возрастной ценз';
    }

    public function description(): ?string {
        return 'Все возрастные цензы, доступные на платформе';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable {
        return [
            Link::make(__('Add'))
                ->icon('plus')
                ->route('platform.systems.ageLimits.create'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable {
        return [
            Layout::table('ageLimits',
                [
                    TD::make(__('#'))
                      ->render(function ( AgeLimit $ageLimit, object $loop ) {
                          return $loop->index + 1;
                      }),
                    TD::make(__('number_context'))
                      ->sort(),
                    TD::make(__('text_context'))
                      ->filter(),
                    TD::make(__('updated_at'))
                      ->sort()
                      ->render(function ( AgeLimit $ageLimit ) {
                          return $ageLimit->updated_at->toDateTimeString();
                      }),
                    TD::make(__('Количество уроков'))
                      ->render(function ( AgeLimit $ageLimit ) {
                          return $ageLimit->lessons_count;
                      }),
                    TD::make(__('Количество фрагментов'))
                      ->render(function ( AgeLimit $ageLimit ) {
                          return $ageLimit->fragments_count;
                      }),
                    TD::make(__('actions'))
                      ->render(function ( AgeLimit $ageLimit ) {
                          return DropDown::make()
                                         ->icon('options-vertical')
                                         ->list([
                                             Link::make(__('Edit'))
                                                 ->icon('pencil')
                                                 ->route('platform.systems.ageLimits.edit', [
                                                     'ageLimit' => $ageLimit->id,
                                                 ]),
                                             Button::make(__('Delete'))
                                                   ->icon('trash')
                                                   ->canSee(($ageLimit->fragments_count === 0) &&
                                                       ($ageLimit->lessons_count === 0))
                                                   ->method('remove', ['id' => $ageLimit->id]),
                                         ]);
                      }),
                ]),
        ];
    }

    public function remove( Request $request ) {
        AgeLimit::findOrFail($request->input('id'))->delete();
        Toast::success(__("Возрастной ценз успешно удалён!"));
    }
}
