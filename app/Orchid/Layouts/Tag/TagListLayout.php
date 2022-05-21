<?php

namespace App\Orchid\Layouts\Tag;

use App\Models\Tag;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use phpDocumentor\Reflection\Utils;

class TagListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'tags';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable {
        return [
            TD::make('#')->render(function ( Tag $tag, object $loop ) {
                return $loop->index + 1;
            }),
            TD::make('value')
              ->sort()
              ->filter(Input::make())
              ->render(function ( Tag $tag ) {
                  return ModalToggle::make($tag->value)
                                    ->modal('asyncEditTagModal')
                                    ->modalTitle(__(__('Новое значение')))
                                    ->method('save')
                                    ->asyncParameters(['tag' => $tag->id]);
              }),
            TD::make('updated_at', __('Последнее редактирование'))
              ->sort()
              ->filter()
              ->render(function ( Tag $tag ) {
                  return $tag->updated_at->toDateTimeString();
              }),
            TD::make(__('Количество привязанных фрагментов'))
              ->sort()
              ->render(function ( Tag $tag ) {
                  return (int)$tag->fragments_count;
              }),
            TD::make(__('Количество привязанных уроков'))
              ->sort()
              ->render(function ( Tag $tag ) {
                  return (int)$tag->lessons_count;
              }),
            TD::make(__('Actions'))
              ->align(TD::ALIGN_CENTER)
              ->render(function ( Tag $tag ) {
                  return DropDown::make()
                                 ->icon('options-vertical')
                                 ->list([
                                     Link::make(__('Edit'))
                                         ->icon('pencil')
                                         ->route('platform.systems.tags.edit', ['tag' => $tag->id]),
                                     Button::make(__('Delete'))
                                           ->icon('trash')
                                         //->canSee(((int)$tag->lessons_count === 0) && ((int)$tag->fragments_count
                                         //=== 0))
                                           ->method('remove', ['id' => $tag->id]),
                                 ]);
              }),
        ];
    }
}
