<?php

namespace App\Http\Requests\Api\Fragment;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexFragmentRequest extends FormRequest
{
    public function rules(): array {
        $tags = Tag::getValues();
        return [
            'title' => ['nullable', 'string'],
            'type'  => ['nullable', 'string', 'in:article,test,video,image'],
            'tags'  => ['nullable', 'array', Rule::in($tags)],
        ];
    }

    public function messages() {
        return [
            'string'  => 'Введены некорректные символы',
            'in'      => 'Выбран несуществующий тип фрагмента. Доступны следующие значения: :values',
            'tags.in' => 'Данное поле должно содержать только следующие значения: :values',
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
