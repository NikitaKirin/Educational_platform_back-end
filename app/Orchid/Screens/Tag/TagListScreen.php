<?php

namespace App\Orchid\Screens\Tag;

use App\Models\Tag;
use App\Orchid\Layouts\Tag\TagEditLayout;
use App\Orchid\Layouts\Tag\TagListLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class TagListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable {
        return [
            'tags' => Tag::filters()->defaultSort('value')->withCount([
                'fragments',
                'lessons',
            ])->paginate(5),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return 'Тег';
    }

    public function description(): ?string {
        return 'Все созданные теги';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable {
        return [
            Link::make(__('Add'))
                ->icon('plus')
                ->route('platform.systems.tags.create'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable {
        return [
            TagListLayout::class,

            Layout::modal('asyncEditTagModal', TagEditLayout::class)
                  ->async('asyncGetTag')
                  ->applyButton('Сохранить'),
        ];
    }

    public function asyncGetTag( Tag $tag ) {
        return [
            'tag' => $tag,
        ];
    }

    public function saveTag( Request $request, Tag $tag ) {
        $request->validate([
                'tag.value' => ['required', Rule::unique('tags', 'value')],
            ]
            , [
                'required'         => 'Это поле обязательно для заполнения',
                'tag.value.unique' => 'Такой тег уже существует!',
            ]);

        $tag->fill(['value' => $request->input('tag.value')]);

        $tag->save();

        Toast::success(__('Тег успешно сохранен!'));
    }

    public function remove( Request $request ) {
        Tag::findOrFail($request->input('id'))->delete();
        Toast::success('Тег успешно удалён!');
    }
}
