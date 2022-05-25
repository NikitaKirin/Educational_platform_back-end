<?php

namespace App\Orchid\Screens\Fragment;

use App\Models\Fragment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\SimpleMDE;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FragmentProfileScreen extends Screen
{
    public Fragment $fragment;

    /**
     * Query data.
     *
     * @return array
     */
    public function query( Request $request, Fragment $fragment ): iterable {
        return [
            'fragment' => $fragment,
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return sprintf("Фрагмент - \"%s\"", $this->fragment->title);
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
            Layout::block([
                Layout::rows([
                    Input::make('fragment.title')
                         ->type('text')
                         ->max(255)
                         ->title('Название фрагмента')
                         ->required(),
                    Quill::make('content')
                         ->canSee($this->fragment->fragmentgable_type === 'article')
                         ->toolbar(["text", "color", "header", "list", "format"])
                         ->value($this->fragment->fragmentgable->content)
                         ->title('Содержимое статьи')
                         ->required(),
                ]),
            ])
                  ->title(__('Основная информация'))
                  ->description(__('Основные данные фрагмента'))
                  ->commands([
                      Button::make(__('Save'))
                            ->type(Color::SUCCESS())
                            ->icon('save')
                            ->method('saveFragment'),
                  ]),
        ];
    }

    public function saveFragment( Request $request, Fragment $fragment ) {
        //dd($fragment);
        DB::transaction(function () use ( $request, $fragment ) {
            if ( $fragment->fragmentgable_type === 'article' ) {
                $fragment->fill([
                    'title' => $request->input('fragment.title'),
                ])->save();
                $fragment->fragmentgable->fill([
                    'content' => $request->input('content'),
                ])->save();
            }
        });

        Toast::success('Фрагмент успешно сохранен!');
    }
}
