<?php

namespace App\Http\Requests\Api\Lesson;

use App\Models\Fragment;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateLessonRequest extends FormRequest
{
    public function rules(): array {
        $user = Auth::user();
        $fragments_id = $user->role == 'student' ? $user->favouriteFragments()->pluck('id') : $user->fragments()
                                                                                                   ->pluck('id');
        $tags = Tag::all()->pluck('id');
        return [
            'title'      => ['required', 'string'],
            'annotation' => ['required', 'string'],
            'fragments'  => ['required', 'array', Rule::in($fragments_id)],
            'tags'       => ['nullable', 'array', Rule::in($tags)],
        ];
    }

    public function messages(): array {
        return [
            'required'     => 'Данное поле является обязательным для заполнения',
            'string'       => 'Значение должно быть строкой',
            'fragments.in' => 'Переданы фрагменты, которые нельзя добавить в урок',
            'tags.in'      => 'Переданы не существующие теги: :values',
        ];
    }

    public function authorize(): bool {
        return Auth::user()->role == 'creator' || Auth::user()->role == 'student';
    }
}
