<?php

namespace App\Http\Requests\Api\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array {
        return [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string',
        ];
    }

    public function messages() {
        return [
            'required'     => 'Это поле обязательно для заполнения',
            'email'        => 'Введен некорректный email',
            'email.unique' => 'Данный email уже занят',
            'string'       => 'Данное поле содержит некорректные символы',
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
