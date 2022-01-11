<?php

namespace App\Http\Requests\Api\Fragment;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFragmentRequest extends FormRequest
{
    public function rules(): array {
        $tags = Tag::getValues();
        return [
            'title' => 'nullable|string',
            'tags'  => ['nullable', 'array', Rule::in($tags)],
            'fon'   => ['nullable', 'image', 'mimes:jpg,png,jpeg,gif'],
        ];
    }

    public function messages(): array {
        return [
            'required'  => 'Данное поле обязательно для заполнения',
            'string'    => 'Введены недоступные символы',
            'array'     => 'На вход ожидался массив',
            'tags.in'   => 'Данное поле должно содержать только следующие значения: :values',
            'fon.image' => 'На вход ожидалось изображение',
            'fon.mimes' => 'Доступны файлы только следующего расширения :values',
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
                $this->validate(['content' => 'nullable|file|mimes:mp4,ogx,oga,ogv,ogg,webm,qt,mov|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4'], [
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
        });
    }
}
