<?php

namespace App\Http\Requests\Api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnAvatarRequest extends FormRequest
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

    public function authorize(): bool {
        return true;
    }
}
