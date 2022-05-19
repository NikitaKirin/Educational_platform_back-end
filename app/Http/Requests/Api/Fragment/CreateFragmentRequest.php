<?php

namespace App\Http\Requests\Api\Fragment;

use App\Models\AgeLimit;
use App\Models\GameType;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CreateFragmentRequest extends FormRequest
{

    public function rules(): array {
        $tags = Tag::getValues();
        $ageLimits = AgeLimit::all()->pluck('id');
        return [
            'type'     => 'required|in:test,article,video,image,game|string',
            'title'    => 'required|string',
            'content'  => 'required',
            'tags'     => ['nullable', 'array', Rule::in($tags)],
            'ageLimit' => ['required', 'numeric', Rule::in($ageLimits)],
            'fon'      => ['nullable', 'image', 'mimes:jpg,png,jpeg,gif'],
        ];
    }

    public function messages(): array {
        return [
            'required'    => 'Данное поле обязательно для заполнения',
            'string'      => 'Введены недопустимые символы',
            'numeric'     => 'На вход ожидалось число',
            'type.in'     => 'Поддерживаются следующие типы фрагментов: :values',
            'array'       => 'На вход ожидался массив',
            'tags.in'     => 'Данное поле должно содержать только следующие значения: :values',
            'fon.image'   => 'На вход ожидалось изображение',
            'fon.mimes'   => 'Доступны файлы только следующего расширения :values',
            'ageLimit.in' => 'На вход ожидалось одно из следующих значений: :values',
        ];
    }

    public function authorize(): bool {
        return true;
    }

    public function withValidator( $validator ) {
        $validator->after(function ( $validator ) {
            if ( $this->input('type') == 'article' ) {
                $this->validate(['content' => 'string'], ['string' => 'Введены недопустимые символы']);
            }
            elseif ( $this->input('type') == 'test' ) {
                $this->validate(['content' => 'json'], ['json' => 'Ожидались данные в формате JSON']);
            }
            elseif ( $this->input('type') == 'video' ) {
                $this->validate(['content' => 'file|mimes:mp4,ogx,oga,ogv,ogg,webm,qt,mov|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4'],
                    [
                        'file'      => 'На вход ожидался файл',
                        'mimes'     => 'Поддерживаются файлы со следующими расширениями: :values',
                        'mimetypes' => 'Поддерживаются файлы следующего формата :values',
                    ]);
            }
            elseif ( $this->input('type') == 'image' ) {
                $this->validate(['content' => 'file|mimes:png,jpg,jpeg,gif', 'annotation' => 'nullable|string'], [
                    'file'  => 'На вход ожидался файл',
                    'mimes' => 'Поддерживаются файлы со следующими расширениями: :values',
                ]);
            }
            elseif ( $this->input('type') == 'game' ) {
                $this->validate([
                    'gameType' => ['required', 'string', Rule::in(GameType::getTitlesTypes())],
                ], [
                    'string'      => 'На вход ожидалась строка',
                    'required'    => 'Данное поле обязательно для заполнения',
                    'gameType.in' => 'Ожидаются только следующие типы игр: :values',
                ]);
                if ( $this->input('gameType') === 'pairs' || $this->input('gameType') === 'sequences' ) {
                    $this->validate([
                        'content'   => 'required|array',
                        'content.*' => 'file|mimes:png,jpg,jpeg,gif',
                    ], [
                        'string'   => 'На вход ожидалась строка',
                        'array'    => 'На вход ожидался массив',
                        'required' => 'Данное поле обязательно для заполнения',
                        'file'     => "На вход ожидался набор файлов",
                        'mimes'    => 'Поддерживаются файлы со следующими расширениями: :values',
                    ]);
                }
                elseif ( $this->input('gameType') === 'matchmaking' ) {
                    $this->validate([
                        'content'     => 'required|array',
                        'content.*'   => 'required|array',
                        'content.*.*' => 'file|mimes:png,jpg,jpeg,gif',
                    ],
                        [
                            'string'   => 'На вход ожидалась строка',
                            'array'    => 'На вход ожидался массив',
                            'required' => 'Данное поле обязательно для заполнения',
                            'file'     => "На вход ожидался набор файлов",
                            'mimes'    => 'Поддерживаются файлы со следующими расширениями: :values',
                        ]);
                }
                elseif ( $this->input('gameType') === 'puzzles' ) {
                    $this->validate([
                        'content'   => 'required|array',
                        'content.*' => 'image|mimes:png,jpg,jpeg,gif',
                        'cols'      => 'required|numeric',
                        'rows'      => 'required|numeric',
                    ],
                        [
                            'required'      => 'Данное поле обязательно для заполнения',
                            'image'         => 'На вход ожидалось изображение',
                            'content.mimes' => 'Доступны только следующие типы изображений: :values',
                            'numeric'       => 'На вход ожидалось число',
                            'content'       => 'На вход ожидался массив',
                        ]);
                }
            }
        });
    }
}
