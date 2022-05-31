<?php

namespace App\Orchid\Screens\Fragment;

use App\Models\Fragment;
use App\Models\User;
use App\View\Components\Fragment\Image;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\SimpleMDE;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FragmentProfileScreen extends Screen
{
    public Fragment $fragment;

    /**
     * Query data.
     *
     * @return array
     */
    public function query( Request $request, Fragment $fragment ): iterable {
        if ( $fragment->fragmentgable_type === 'image' ) {
            $defaultFonUrl = $fragment->getFirstMediaUrl('fragments_images');
        }
        if ( $fragment->fragmentgable_type === 'game' ) {
            $defaultFonUrl = (empty($fragment->getFirstMediaUrl('fragments_fons'))) ?
                asset('img/fr_fons/' . $fragment->fragmentgable->gameType->type . '.png') :
                $fragment->getFirstMediaUrl('fragments_fons');
        }
        else {
            $defaultFonUrl = asset('img/fr_fons/' . $fragment->fragmentgable_type . '.png');
        }
        return [
            'fragment' => $fragment,
            'imageUrl' => empty($fragment->getFirstMediaUrl('fragments_fons')) ? $defaultFonUrl :
                $fragment->getFirstMediaUrl('fragments_fons'),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return sprintf("Фрагмент - \"%s\"", $this->fragment->title);
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
            Layout::block([
                Layout::component(Image::class),
            ])
                  ->title(__('Обложка фрагмента'))
                  ->description(__('Текущая обложка')),
            Layout::block([
                Layout::rows([
                    Input::make('fragment.title')
                         ->type('text')
                         ->max(255)
                         ->title('Название фрагмента')
                         ->required(),
                    Input::make('annotation')
                         ->value($this->fragment->fragmentgable->annotation)
                         ->type('text')
                         ->max(255)
                         ->title('Аннотация')
                         ->canSee($this->fragment->fragmentgable_type === 'image')
                         ->required(),
                    Quill::make('content')
                         ->canSee($this->fragment->fragmentgable_type === 'article')
                         ->toolbar(["text", "color", "header", "list", "format"])
                         ->value($this->fragment->fragmentgable->content)
                         ->title('Содержимое статьи')
                         ->required(),
                    Input::make('content')
                         ->type('file')
                         ->canSee($this->fragment->fragmentgable_type === 'image')
                         ->title('Новое изображение'),
                    Input::make('content')
                         ->type('file')
                         ->canSee($this->fragment->fragmentgable_type === 'video')
                         ->title(__('Видео')),
                    /*Cropper::make('fon')
                           ->targetId()
                           ->title(__('Новая обложка фрагмента')),*/
                ]),
            ])
                  ->title(__('Основная информация'))
                  ->description(__('Основные данные фрагмента'))
                  ->commands([
                      Button::make(__('Save'))
                            ->type(Color::SUCCESS())
                            ->icon('save')
                            ->method('saveFragment'),
                  ]),
        ];
    }

    public function saveFragment( Request $request, Fragment $fragment ) {
        $attachment = Attachment::findOrFail($request->input('fon'));
        DB::transaction(function () use ( $request, $fragment, $attachment ) {
            if ( $fragment->fragmentgable_type === 'article' ) {
                $fragment->fill([
                    'title' => $request->input('fragment.title'),
                ])->save();
                $fragment->fragmentgable->fill([
                    'content' => $request->input('content'),
                ])->save();
            }
            $fragment->addMedia(base_path($attachment->path . $attachment->name . '.' . $attachment->extension))
                     ->toMediaCollection('fragments_games');
        });

        Toast::success('Фрагмент успешно сохранен!');
    }
}
