<?php

namespace App\Orchid\Layouts\Fragment;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\SimpleMDE;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class FragmentEditLayout extends Rows
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
            Input::make('fragment.title')
                 ->type('text')
                 ->max(255)
                 ->required(),
            SimpleMDE::make('content')
                     ->canSee('fragment.fragmentgable_type' === 'article'),
        ];
    }
}
