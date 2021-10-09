<?php

namespace App\Http\Requests\Api\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RegisterRequest extends FormRequest
{
    public function rules(): array {
        return [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string',
            'role'     => 'required|in:creator,student,admin',
        ];
    }

    public function messages(): array {
        return [
            'required'     => 'Данное поле обязательно для заполнения',
            'email'        => 'Введен некорректный email',
            'email.unique' => 'Данный email уже занят',
            'string'       => 'Данное поле содержит некорректные символы',
        ];
    }

    // Создать нового пользователя в рамках авторизации может только администратор
    public function authorize(): bool {
        return Auth::user()->role == 'admin';
    }
}
