<?php

namespace App\Orchid\Layouts\Fragment;

use App\Models\Fragment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FragmentListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'fragments';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable {
        return [
            TD::make('id')
              ->sort()
              ->render(function ( Fragment $fragment ) {
                  $fonPath = (empty($fragment->getFirstMediaUrl('fragments_fons'))) ?
                      asset('img/fr_fons/' . $fragment->fragmentgable_type . '.png') :
                      $fragment->getFirstMediaUrl('fragments_fons');
                  if ( $fragment->fragmentgable_type === 'image' ) {
                      $fonPath = $fragment->fragmentgable->content;
                  }
                  if ( $fragment->fragmentgable_type === 'game' ) {
                      $fonPath = (empty($fragment->getFirstMediaUrl('fragments_fons'))) ?
                          asset('img/fr_fons/' . $fragment->fragmentgable->gameType->type . '.png') :
                          $fragment->getFirstMediaUrl('fragments_fons');
                  }
                  return sprintf("<img src='%s' width='100px' class='mw-100 d-block img-fluid'/>
<span class='small text-muted mt-1 mb-0'>%s</span>", $fonPath, $fragment->id);
              })
              ->filter()
              ->sort('id'),
            TD::make('fragmentgable_type', __('Тип фрагмента'))
              ->sort()
              ->filter(),
            TD::make('title', __('Название'))
              ->filter()
              ->sort()
              ->render(function ( Fragment $fragment ) {
                  return Link::make($fragment->title)
                             ->icon('pencil')
                             ->route('platform.systems.fragments.profile', $fragment);
              }),
            TD::make('age_limit_id', __('Возрастной ценз'))
              ->sort()
              ->render(function ( Fragment $fragment ) {
                  return $fragment->ageLimit->text_context;
              }),
            TD::make(__('Автор'))
              ->render(function ( Fragment $fragment ) {
                  return Link::make($fragment->user->name)
                             ->icon('user')
                             ->route('platform.systems.users.edit', $fragment->user);
              }),
            TD::make('updated_at', __("Последние изменения"))
              ->sort()
              ->filter()
              ->render(function ( Fragment $fragment ) {
                  return $fragment->updated_at->toDateTimeString();
              }),
            TD::make(__('Actions'))
              ->render(function ( Fragment $fragment ) {
                  return DropDown::make()
                                 ->icon('options-vertical')
                                 ->list([
                                     Link::make(__('Edit'))
                                         ->icon('pencil')
                                         ->route('platform.systems.fragments.profile', ['fragment' => $fragment->id]),
                                     Button::make(__('Delete'))
                                           ->icon('trash')
                                           ->method('remove', ['id' => $fragment->id])
                                           ->confirm(__('Вы уверены, что хотите удалить фрагмент? Отменить данное действие будет невозможно.')),
                                 ]);
              }),
        ];
    }
}
