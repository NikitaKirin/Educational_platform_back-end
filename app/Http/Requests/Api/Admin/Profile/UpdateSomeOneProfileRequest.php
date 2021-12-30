<?php

namespace App\Http\Requests\Api\Admin\Profile;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSomeOneProfileRequest extends FormRequest
{
    public function rules(): array {
        $today = Carbon::today()->format('d.m.Y');
        return [
            'name'     => 'required|string',
            'birthday' => ['nullable', 'date', "before:{$today}"],
            'role'     => 'nullable|in:creator,student,admin',
        ];
    }

    public function messages(): array {
        return [
            'required'        => 'Это поле обязательно для заполнения',
            'string'          => 'Данное поле содержит некорректные символы',
            'date'            => 'Указан неверный формат даты',
            'birthday.before' => "Дата не может быть позднее, чем сегодня",
        ];
    }

    // Изменение данных чужого профиля доступно только администратору
    public function authorize(): bool {
        return Auth::user()->role == 'admin';
    }
}
