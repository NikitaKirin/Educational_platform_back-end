<?php

namespace App\Http\Requests\Api\Fragment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFragmentRequest extends FormRequest
{
    public function rules(): array {
        return [
            'title' => 'required|string',
        ];
    }

    public function messages(): array {
        return [
            'required' => 'Данное поле обязательно для заполнения',
            'string'   => 'Введены недоступные символы',
        ];
    }

    public function authorize(): bool {
        return true;
    }

    public function withValidator( $validator ) {
        $validator->after(function ( $validator ) {
            if ( $this->input('type') == 'article' ) {
                $this->validate(['content' => 'required|string'], [
                    'string'   => 'Введены недопустимые символы',
                    'required' => 'Данное поле обязательно для заполнения',
                ]);
            }
            elseif ( $this->input('type') == 'test' ) {
                $this->validate(['content' => 'required|json'], [
                    'json'     => 'Ожидались данные в формате JSON',
                    'required' => 'Данное поле обязательно для заполнения',
                ]);
            }
            /*elseif ( $this->input('type') == 'video' ) {
                $this->validate(['content' => 'file|mimes:mp4,ogx,oga,ogv,ogg,webm,qt,mov|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4'], [
                    'file'      => 'На вход ожидался файл',
                    'mimes'     => 'Поддерживаются файлы со следующими расширениями: :values',
                    'mimetypes' => 'Поддерживаются файлы следующего формата :values',
                ]);
            }*/
        });
    }
}
