<?php

namespace App\Http\Requests\Api\User\Profile;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateOwnProfileRequest extends FormRequest
{
    public function rules(): array {
        $today = Carbon::today()->format('d.m.Y');
        $role = Auth::user()->role;
        return [
            'name'     => 'required|string',
            'birthday' => ['nullable', 'date', "before:{$today}"],
        ];
    }

    public function messages(): array {
        return [
            'required'        => 'Данное поле обязательное для заполнения',
            'string'          => 'Данное поле содержит некорректные символы',
            'date'            => 'Введен некорректный формат даты',
            'birthday.before' => "Дата не может быть позднее, чем сегодня",
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
