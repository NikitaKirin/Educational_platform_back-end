<?php

namespace App\Orchid\Screens\Fragment;

use App\Models\Fragment;
use App\Orchid\Layouts\Fragment\FragmentEditLayout;
use Orchid\Screen\Screen;

class FragmentEditScreen extends Screen
{
    public Fragment $fragment;

    /**
     * Query data.
     *
     * @return array
     */
    public function query( Fragment $fragment ): iterable {
        return [
            'fragment' => $fragment->load('fragmentgable')->withCount(['fragmentgable']),
            'content'  => $fragment->fragmentgable,
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return $this->fragment->exists ? "Изменить фрагмент" : 'Создать фрагмент';
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
            FragmentEditLayout::class,
        ];
    }
}
