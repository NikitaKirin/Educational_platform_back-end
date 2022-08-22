<?php

namespace App\Orchid\Screens\Lesson;

use App\Models\Lesson;
use App\Orchid\Layouts\Lessons\LessonListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class LessonListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable {
        return [
            'lessons' => Lesson::filters()
                               ->defaultSort('title')
                               ->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return 'Урок';
    }

    public function description(): ?string {
        return 'Уроки доступные на платформе';
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
            LessonListLayout::class,
        ];
    }

    public function remove( Request $request ) {
        Lesson::findOrFail($request->input('id'))->delete();
        Toast::success('Урок успешно удален');
    }

}
