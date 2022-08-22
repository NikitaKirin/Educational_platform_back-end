<?php

namespace App\Orchid\Layouts\Tag;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TagEditLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable {
        return [
            Input::make('tag.value')
                 ->type('text')
                 ->max(255)
                 ->required()
                 ->title(__('Значение'))
                 ->placeholder(__('Введите значение тега')),
        ];
    }
}
