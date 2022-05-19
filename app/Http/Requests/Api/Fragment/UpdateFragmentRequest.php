<?php

namespace App\Http\Requests\Api\Fragment;

use App\Models\AgeLimit;
use App\Models\Fragment;
use App\Models\GameType;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFragmentRequest extends FormRequest
{
    /**
     * @var Fragment $fragment Текущий объект фрагмента
     */
    private Fragment $fragment;

    /**
     * @var string $gameType тип игры, если имеется
     */
    private string $gameType;

    public function rules(): array {
        $this->fragment = $this->route('fragment');
        if ( $this->fragment->fragmentgable_type === 'game' ) {
            $this->gameType = GameType::find($this->fragment->fragmentgable->game_type_id)->type ?? null;
        }
        $ageLimits = AgeLimit::all()->pluck('id');
        $tags = Tag::getValues();
        return [
            'title'    => 'nullable|string',
            'tags'     => ['nullable', 'array', Rule::in($tags)],
            'ageLimit' => ['nullable', 'numeric', Rule::in($ageLimits)],
            'fon'      => ['nullable', 'image', 'mimes:jpg,png,jpeg,gif'],
        ];
    }

    public function messages(): array {
        return [
            'required'    => 'Данное поле обязательно для заполнения',
            'string'      => 'Введены недоступные символы',
            'numeric'     => 'На вход ожидалось число',
            'array'       => 'На вход ожидался массив',
            'tags.in'     => 'Данное поле должно содержать только следующие значения: :values',
            'ageLimit.in' => 'На вход ожидалось одно из следующих значений: :values',
            'fon.image'   => 'На вход ожидалось изображение',
            'fon.mimes'   => 'Доступны файлы только следующего расширения :values',
        ];
    }

    public function authorize(): bool {
        return true;
    }

    public function withValidator( $validator ) {
        $validator->after(function ( $validator ) {
            $fragmentgable_type = $this->fragment->fragmentgable_type;
            if ( $fragmentgable_type == 'article' ) {
                $this->validate(['content' => 'nullable|string'], [
                    'string'   => 'Введены недопустимые символы',
                    'required' => 'Данное поле обязательно для заполнения',
                ]);
            }
            elseif ( $fragmentgable_type == 'test' ) {
                $this->validate(['content' => 'nullable|json'], [
                    'json'     => 'Ожидались данные в формате JSON',
                    'required' => 'Данное поле обязательно для заполнения',
                ]);
            }
            elseif ( $fragmentgable_type == 'video' ) {
                $this->validate(['content' => 'nullable|file|mimes:mp4,ogx,oga,ogv,ogg,webm,qt,mov|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4'],
                    [
                        'file'      => 'На вход ожидался файл',
                        'mimes'     => 'Поддерживаются файлы со следующими расширениями: :values',
                        'mimetypes' => 'Поддерживаются файлы следующего формата: :values',
                    ]);
            }
            elseif ( $fragmentgable_type == 'image' ) {
                $this->validate([
                    'content'    => 'nullable|file|mimes:png,jpg,jpeg,gif',
                    'annotation' => 'nullable|string',
                ], [
                    'file'  => 'На вход ожидался файл',
                    'mimes' => 'Поддерживаются файлы со следующими расширениями: :values',
                ]);
            }
            elseif ( $fragmentgable_type === 'game' ) {
                if ( $this->gameType === 'pairs' || $this->gameType === 'sequences' ) {
                    $this->validate([
                        'content'        => 'nullable|array',
                        'content.*'      => 'file|mimes:png,jpg,jpeg,gif',
                        'metaImagesData' => 'required|json',
                    ], [
                        'string'   => 'На вход ожидалась строка',
                        'array'    => 'На вход ожидался массив',
                        'required' => 'Данное поле обязательно для заполнения',
                        'file'     => "На вход ожидался набор файлов",
                        'mimes'    => 'Поддерживаются файлы со следующими расширениями: :values',
                        'json'     => 'На вход ожидались данные в формате json',
                    ]);
                }
                elseif ( $this->gameType === 'matchmaking' || $this->gameType === 'puzzles' ) {
                    $this->validate([
                        'content'        => 'nullable|array',
                        'metaImagesData' => 'required|json',
                        //'content.*' => 'nullable|array',
                        'content.*'      => 'file|mimes:png,jpg,jpeg,gif',
                    ],
                        [
                            'string'   => 'На вход ожидалась строка',
                            'array'    => 'На вход ожидался массив',
                            'required' => 'Данное поле обязательно для заполнения',
                            'file'     => "На вход ожидался набор файлов",
                            'mimes'    => 'Поддерживаются файлы со следующими расширениями: :values',
                            'json'     => 'На вход ожидались данные в формате json',
                        ]);
                }
            }
        });
    }
}
