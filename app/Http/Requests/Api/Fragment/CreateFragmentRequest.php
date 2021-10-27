<?php

namespace App\Http\Requests\Api\Fragment;

use Illuminate\Foundation\Http\FormRequest;

class CreateFragmentRequest extends FormRequest
{
    public function rules(): array {
        return [
            'type'    => 'required|in:test,article,video|string',
            'title'   => 'required|string',
            'content' => 'required',
        ];
    }

    public function messages(): array {
        return [
            'required' => 'Данное поле обязательно для заполнения',
            'string'   => 'Введены недопустимые символы',
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
                $this->validate(['content' => 'file|mimes:mp4,ogx,oga,ogv,ogg,webm,qt|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4'], [
                    'file'      => 'На вход ожидался файл',
                    'mimes'     => 'Поддерживаются файлы со следующими расширениями: :values',
                    'mimetypes' => 'Поддерживаются файлы следующего формата :values',
                ]);
            }
        });
    }
}
