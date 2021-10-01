<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules() {
        return [
            'name'     => 'required|string',
            'birthday' => 'nullable|date',
            'role'     => 'required|in:creator,student',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string',
        ];
    }

    public function messages() {
        return [
            'email'        => 'Вы ввели некорректный email',
            'required'     => 'Данное поле является обязательным для заполнения',
            'string'       => 'Вы ввели недоступные символы',
            'email.unique' => 'Данный email уже занят',
            'date'         => 'Введены недоступные символы',
        ];
    }

    /*    public function withValidator( $validator ) {
            if ( $validator->fails() ) {
                $errors = $validator->errors();

                return response()->json(["messages" => $errors]);
            }
        }*/

    public function authorize() {
        return true;
    }
}
