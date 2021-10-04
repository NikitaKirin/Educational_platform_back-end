<?php

namespace App\Http\Requests\Api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateOwnProfileRequest extends FormRequest
{
    public function rules(): array {
        $role = Auth::user()->role;
        return [
            'name'     => 'required|string',
            'birthday' => 'nullable|date',
        ];
    }

    public function messages(): array {
        return [
            'required' => 'Данное поле обязательное для заполнения',
            'string'   => 'Данное поле содержит некорректные символы',
            'date'     => 'Введен некорректный формат даты',
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
