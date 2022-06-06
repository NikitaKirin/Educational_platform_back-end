<?php

namespace App\Orchid\Screens\Fragment;

use App\Models\AgeLimit;
use App\Models\Fragment;
use App\Models\Tag;
use App\Models\User;
use App\View\Components\Fragment\Image;
use App\View\Components\Fragment\Video;
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
            $defaultFonUrl = $fragment->fragmentgable->getFirstMediaUrl('fragments_images');
        }
        elseif ( $fragment->fragmentgable_type === 'video' ) {
            $videoUrl = $fragment->fragmentgable->getFirstMediaUrl('fragments_videos');
        }
        elseif ( $fragment->fragmentgable_type === 'game' ) {
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
            'videoUrl' => $videoUrl ?? null,
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
                  ->description(__('Текущая обложка'))
                  ->canSee($this->fragment->fragmentgable_type !== 'image'),
            Layout::block([
                Layout::rows([
                    Input::make('fragment.title')
                         ->type('text')
                         ->max(255)
                         ->title('Название фрагмента')
                         ->required(),
                    Input::make('fragment.annotation')
                         ->value($this->fragment->fragmentgable->annotation)
                         ->type('text')
                         ->max(255)
                         ->title('Аннотация')
                         ->canSee($this->fragment->fragmentgable_type === 'image')
                         ->required(),
                    Quill::make('fragment.content')
                         ->canSee($this->fragment->fragmentgable_type === 'article')
                         ->toolbar(["text", "color", "header", "list", "format"])
                         ->value($this->fragment->fragmentgable->content)
                         ->title('Содержимое статьи')
                         ->required(),
                    Input::make('fragment.content')
                         ->type('file')
                         ->canSee($this->fragment->fragmentgable_type === 'image')
                         ->title('Новое изображение'),
                    Input::make('fragment.content')
                         ->type('file')
                         ->canSee($this->fragment->fragmentgable_type === 'video')
                         ->title(__('Видео')),
                    Input::make('fon')
                         ->type('file')
                         ->title(__('Новая обложка фрагмента'))
                         ->canSee($this->fragment->fragmentgable_type !== 'image'),
                    Relation::make('fragment.ageLimit')
                            ->fromModel(AgeLimit::class, 'text_context')
                            ->title(__('Выберите возрастной ценз')),
                    Relation::make('fragment.tags.')
                            ->fromModel(Tag::class, 'value')
                            ->multiple()
                            ->title(__('Выберите теги')),
                ]),

                Layout::block([
                    Layout::component(Image::class)->canSee($this->fragment->fragmentgable_type === 'image'),
                    Layout::component(Video::class)
                          ->canSee($this->fragment->fragmentgable_type === 'video'),
                ])
                      ->description(__('Текущий медиа-ресурс фрагмента'))
                      ->title(__('Медиа'))
                      ->canSee($this->fragment->fragmentgable_type === 'video' ||
                          $this->fragment->fragmentgable_type === 'image'),
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
        DB::transaction(function () use ( $request, $fragment ) {
            $fragment->update([
                'title' => $request->input('fragment.title') ?? $fragment->title,
            ]);
            if ( $ageLimitId = $request->input('fragment.ageLimit') ) {
                $fragment->ageLimit()->associate($ageLimitId)->save();
            }
            if ( $tags = $request->input('fragment.tags') ) {
                $tags = array_unique($tags);
                $fragment->tags()->sync($tags);
            }
            if ( $fragment->fragmentgable_type === 'article' ) {
                $fragment->fragmentgable->update([
                    'content' => $request->input('fragment.content') ?? $fragment->fragmentgable->content,
                ]);
            }
            if ( $fragment->fragmentgable_type === 'image' || $fragment->fragmentgable_type === 'video' ) {
                if ( $request->hasFile('fragment.content') ) {
                    $fragment->fragmentgable->clearMediaCollection("fragments_{$fragment->fragmentgable_type}s");
                    $fragment->fragmentgable->addMediaFromRequest('fragment.content')
                                            ->toMediaCollection("fragments_{$fragment->fragmentgable_type}s",
                                                'fragments');
                    $fragment->fragmentgable->update(['content' => $fragment->fragmentgable->getFirstMediaUrl("fragments_{$fragment->fragmentgable_type}s")]);
                }

                if ( $fragment->fragmentgable_type == 'image' ) {
                    $fragment->fragmentgable->update(['annotation' => $request->input('fragment.annotation')]);
                }
            }
            if ( $request->hasFile('fon') ) {
                if ( empty($fragment->getFirstMediaUrl('fragments_fons')) )
                    $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
                else {
                    $fragment->clearMediaCollection('fragments_fons');
                    $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
                }
            }
        });

        Toast::success('Фрагмент успешно сохранен!');
    }
}
