<?php

namespace App\Http\Requests\Api\User\Auth;

use App\Http\Requests\Api\ApiFormRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array {
        $today = Carbon::today()->format('d.m.Y');
        return [
            'name'     => 'required|string',
            'birthday' => ['nullable', 'date', "before:{$today}"],
            'role'     => 'required|in:creator,student',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string',
        ];
    }

    public function messages(): array {
        return [
            'email'           => 'Вы ввели некорректный email',
            'required'        => 'Данное поле обязательно для заполнения',
            'string'          => 'Вы ввели недоступные символы',
            'email.unique'    => 'Данный email уже занят',
            'date'            => 'Введены недоступные символы',
            'birthday.before' => "Дата не может быть позднее, чем сегодня",
            'role.in'         => "Доступны только следующие типы ролей: :values"
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
