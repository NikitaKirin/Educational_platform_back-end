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

    public function messages() {
        return [
            'required' => 'Данное поле обязательно для заполнения',
            'string'   => 'Введены недоступные символы',
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
