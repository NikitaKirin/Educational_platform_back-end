<?php

namespace App\Http\Requests\Api\Fragment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFragmentRequest extends FormRequest
{
    public function rules(): array {
        return [
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
