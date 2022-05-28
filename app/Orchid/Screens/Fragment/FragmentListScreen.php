<?php

namespace App\Orchid\Screens\Fragment;

use App\Models\Fragment;
use App\Orchid\Layouts\Fragment\FragmentListLayout;
use Orchid\Screen\Screen;

class FragmentListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable {
        return [
            'fragments' => Fragment::filters()
                                   ->defaultSort('title')
                                   ->paginate(15),
            'foo' => 'bar',
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return 'Все фрагменты';
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
            FragmentListLayout::class,
        ];
    }
}
