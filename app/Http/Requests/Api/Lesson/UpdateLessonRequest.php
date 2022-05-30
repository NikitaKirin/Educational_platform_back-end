<?php

namespace App\Http\Requests\Api\Lesson;

use App\Models\AgeLimit;
use App\Models\Fragment;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function rules(): array {
        $user = Auth::user();
        //$lesson = Lesson::where('id', $this->input('lesson'));
        $fragments_id = $user->role == 'student' ? $user->favouriteFragments()
                                                        ->pluck('fragments.id') : $user->fragments()->pluck('id');
        $tags = Tag::all()->pluck('id');
        $ageLimits = AgeLimit::all()->pluck('id');
        return [
            'title'      => ['required', 'string'],
            'annotation' => ['required', 'string'],
            'fragments'  => ['required', 'array', Rule::in($fragments_id)],
            'ageLimit'   => ['nullable', 'numeric', Rule::in($ageLimits)],
            'tags'       => ['nullable', 'array', Rule::in($tags)],
            'fon'        => ['nullable', 'image', 'mimes:jpg,png,jpeg,gif'],
        ];
    }

    public function messages(): array {
        return [
            'required'     => 'Данное поле является обязательным для заполнения',
            'string'       => 'Значение должно быть строкой',
            'numeric'      => 'На вход ожидалось число',
            'ageLimit.in'  => 'На вход ожидалось одно из следующих значений: :values',
            'fragments.in' => 'Переданы фрагменты, которые нельзя добавить в урок',
            'tags.in'      => 'Переданы не существующие теги: :values',
            'fon.image'    => 'На вход ожидалось изображение',
            'fon.mimes'    => 'Доступны файлы только следующего расширения :values',
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
