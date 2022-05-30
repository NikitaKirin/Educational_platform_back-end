<?php

namespace App\Orchid\Screens\Lesson;

use App\Models\Fragment;
use App\Models\Lesson;
use App\Orchid\Layouts\Fragment\FragmentListLayout;
use App\View\Components\Fragment\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Alert\Toast;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class LessonProfileScreen extends Screen
{
    public Lesson $lesson;

    /**
     * Query data.
     *
     * @return array
     */
    public function query( Request $request, Lesson $lesson ): iterable {
        return [
            'lesson'    => $lesson,
            'fragments' => $lesson->fragments()->withPivot('order')->orderBy('fragment_lesson.order')->get(),
            'imageUrl'  => empty($lesson->getFirstMediaUrl('fragments_fons')) ? asset('img/lesson-fon.png') :
                $lesson->getFirstMediaUrl('fragments_fons'),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return sprintf("Урок - '%s'", $this->lesson->title);
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable {
        return [
            Link::make(__('Профиль автора'))
                ->icon('user')
                ->route('platform.systems.users.edit', ['user' => $this->lesson->user_id]),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function layout(): iterable {
        return [
            Layout::block([
                Layout::component(Image::class),
            ])
                  ->title('Обложка урока'),

            Layout::block([
                Layout::rows([
                    Input::make('lesson.title')
                         ->type('text')
                         ->max(255)
                         ->title(__('Название'))
                         ->required(),
                    Input::make('lesson.annotation')
                         ->type('text')
                         ->max(255)
                         ->title(__('Краткое описание'))
                         ->required(),
                    Button::make(__('Save'))
                          ->type(Color::SUCCESS())
                          ->icon('save')
                          ->method('saveMainDataLesson'),
                ]),
            ])
                  ->title(__('Основная информация')),

            Layout::columns([
                FragmentListLayout::class,
                Layout::rows([
                    Relation::make('lesson.fragments.')
                            ->fromModel(Fragment::class, 'title')
                            ->multiple()
                            ->title('Сформируйте набор фрагментов'),
                ]),
            ]),
        ];
    }

    public function saveMainDataLesson( Request $request, Lesson $lesson ) {
        DB::transaction(function () use ( $request, $lesson ) {
            $lesson->fill([
                'title'      => $request->input('lesson.title'),
                'annotation' => $request->input('lesson.annotation'),
            ])->save();
        });

        \Orchid\Support\Facades\Toast::success(__("Урок успешно сохранен!"));
    }
}
