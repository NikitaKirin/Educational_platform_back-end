<?php

namespace App\Http\Requests\Api\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSomeOneAvatarRequest extends FormRequest
{
    public function rules(): array {
        return [
            'avatar' => 'required|image',
        ];
    }

    public function messages() {
        return [
            'avatar.required' => 'Невозможно заменить фото на пустой файл',
            'image'          => 'Указан неверный формат',
        ];
    }

    // Функционал доступен только администратору
    public function authorize(): bool {
        return Auth::user()->role == 'admin';
    }
}
