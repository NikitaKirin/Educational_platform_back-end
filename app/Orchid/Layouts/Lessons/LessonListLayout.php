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
            TD::make('id')
              ->sort()
              ->filter()
              ->render(function ( Lesson $lesson ) {
                  $fonPath = empty($lesson->getFirstMediaUrl('lessons_fons')) ?
                      asset('img/lesson-fon.png') :
                      $lesson->getFirstMediaUrl('lessons_fons');
                  return sprintf("<span>%s</span> <img src='%s' width='100px'", $lesson->id, $fonPath);
              }),
            TD::make('title', __('Название'))
              ->filter()
              ->sort()
              ->render(function ( Lesson $lesson ) {
                  return Link::make($lesson->title)
                             ->icon('pencil')
                             ->route('platform.systems.lessons.profile', $lesson);
              }),
            TD::make('annotation', __('Краткое описание'))
              ->filter(),
            TD::make('age_limit_id', __('Возрастной ценз'))
              ->sort()
              ->render(function ( Lesson $lesson ) {
                  return $lesson->ageLimit->text_context;
              }),
            TD::make('user', __('Автор'))
              ->render(function ( Lesson $lesson ) {
                  return Link::make($lesson->user->name)
                             ->icon('user')
                             ->route('platform.systems.users.edit', $lesson->user);
              }),
            TD::make('updated_at', __('Последние изменения'))
              ->sort()
              ->render(function ( Lesson $lesson ) {
                  return $lesson->updated_at->toDateTimeString();
              }),
            TD::make(__('Actions'))
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
