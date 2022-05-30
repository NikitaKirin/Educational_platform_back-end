<?php

namespace App\Orchid\Layouts\Lessons;

use App\Models\Lesson;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class LessonListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'lessons';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable {
        return [
            TD::make('#')
              ->render(function ( Lesson $lesson, object $loop ) {
                  $fonPath = empty($lesson->getFirstMediaUrl('lessons_fons')) ?
                      asset('img/lesson-fon.png') :
                      $lesson->getFirstMediaUrl('lessons-fons');
                  return sprintf("<span>%s</span> <img src='%s' width='100px'", $loop->index, $fonPath);
              }),
            TD::make('title', __('Название')),
            TD::make('annotation', __('Краткое описание')),
            TD::make('ageLimit', __('Возрастной ценз'))
              ->render(function ( Lesson $lesson ) {
                  return $lesson->ageLimit->text_context;
              }),
            TD::make('user', __('Автор'))
              ->render(function ( Lesson $lesson ) {
                  return "<a href= " . route("platform.systems.users.edit", $lesson->user) . ">" .
                      $lesson->user->name . "</a>";
              }),
            TD::make('updated_at', __('Последние изменения'))
              ->render(function ( Lesson $lesson ) {
                  return $lesson->updated_at->toDateTimeString();
              }),
            TD::make( __('Actions'))
              ->render(function ( Lesson $lesson ) {
                  return DropDown::make()
                                 ->icon('options-vertical')
                                 ->list([
                                     Link::make(__('Edit'))
                                         ->icon('pencil')
                                         ->route('platform.systems.lessons.profile', ["lesson" => $lesson->id]),
                                 ]);
              }),
        ];
    }
}
