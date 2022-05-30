<?php

namespace App\Orchid\Layouts\Fragment;

use App\Models\Fragment;
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
            TD::make('ID')
              ->render(function ( Fragment $fragment ) {
                  $fonPath = (empty($fragment->getFirstMediaUrl('fragments_fons'))) ?
                      asset('img/fr_fons/' . $fragment->fragmentgable_type . '.png') :
                      $fragment->getFirstMediaUrl('fragments_fons');
                  if ( $fragment->fragmentgable_type === 'image' ) {
                      $fonPath = $fragment->fragmentgable->content;
                  }
                  return sprintf("<img src='%s' width='100px' class='mw-100 d-block img-fluid'/>
<span class='small text-muted mt-1 mb-0'>%s</span>", $fonPath, $fragment->id);
              }),
            TD::make('fragmentgable_type', __('Тип фрагмента')),
            TD::make('title', __('Название')),
            TD::make(__('Автор'))->render(function ( Fragment $fragment ) {
                return "<a href= " . route("platform.systems.users.edit", $fragment->user) . ">" .
                    $fragment->user->name . "</a>";
            }),
            TD::make('updated_at', __("Последние изменения"))
              ->render(function ( Fragment $fragment ) {
                  return $fragment->updated_at->toDateTimeString();
              }),
            TD::make(__('Actions'))
              ->render(function ( Fragment $fragment ) {
                  return DropDown::make()
                                 ->icon('options-vertical')
                                 ->list([
                                     Link::make(__('Перейти к фрагменту'))
                                         ->icon('eye')
                                         ->route('platform.systems.fragments.profile', ['fragment' => $fragment->id]),
                                 ]);
              }),
        ];
    }
}
