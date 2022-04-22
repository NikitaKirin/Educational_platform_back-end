<?php

namespace App\Http\Requests\Api\Fragment;

use App\Models\AgeLimit;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexFragmentRequest extends FormRequest
{
    public function rules(): array {
        $tags = Tag::getValues();
        $ageLimits = AgeLimit::all('id')->pluck('id')->toArray();
        return [
            'title'    => ['nullable', 'string'],
            'type'     => ['nullable', 'string', 'in:article,test,video,image, game'],
            'tags'     => ['nullable', 'array', Rule::in($tags)],
            'ageLimit' => ['nullable', 'string', Rule::in($ageLimits)],
        ];
    }

    public function messages() {
        return [
            'string'      => 'Введены некорректные символы',
            'in'          => 'Выбран несуществующий тип фрагмента. Доступны следующие значения: :values',
            'tags.in'     => 'Данное поле должно содержать только следующие значения: :values',
            'ageLimit.in' => 'Возрастной ценз может содержать только следующие значения: :values',
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
