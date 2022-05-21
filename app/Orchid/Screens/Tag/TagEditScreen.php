<?php

namespace App\Orchid\Screens\Tag;

use App\Models\Tag;
use App\Orchid\Layouts\Tag\TagEditLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class TagEditScreen extends Screen
{
    public Tag $tag;

    /**
     * Query data.
     *
     * @return array
     */
    public function query( Tag $tag ): iterable {
        $tag->load(['fragments', 'lessons']);
        return [
            'tag' => $tag,
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return $this->tag->exists ? 'Изменить тег' : 'Создать тег';
    }

    public function description(): ?string {
        return 'Введите название тега';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable {
        return [
            Button::make(__('Remove'))
                  ->icon('trash')
                  ->method('remove')
                  ->canSee($this->tag->exists),

            Button::make(__('Save'))
                  ->icon('check')
                  ->method('save'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable {
        return [
            Layout::block(TagEditLayout::class)
                  ->title(__('Информация о теге'))
                  ->description(__('Обновить информацию'))
                  ->commands(
                      Button::make(__('Save'))
                            ->type(Color::DEFAULT())
                            ->icon('check')
                            ->canSee($this->tag->exists)
                            ->method('save')
                  ),

        ];
    }

    public function save( Request $request, Tag $tag ) {
        $request->validate([
            'tag.value' => ['required', Rule::unique('tags', 'value')],
        ],
            [
                'tag.value.unique' => 'Данный тег уже существует!',
            ]);
        $tag->fill(['value' => $request->input('tag.value')]);
        $tag->save();
        Toast::success(__('Тег успешно сохранен!'));
        return redirect()->route('platform.systems.tags');
    }

    public function remove( Tag $tag ) {
        $tag->delete();

        Toast::success(__('Тег успешно удалён!'));

        return redirect()->route('platform.systems.tags');
    }
}
