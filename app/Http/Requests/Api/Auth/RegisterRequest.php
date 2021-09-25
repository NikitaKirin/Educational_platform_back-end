<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules() {
        return [
            'name'     => 'string|required',
            'birthday' => 'date',
            'role'     => 'required|in:creator,student',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string',
        ];
    }

    public function messages() {
        return [
            'string'       => 'Вы ввели недоступные символы',
            'required'     => 'Данное поле является обязательным для заполнения',
            'email.unique' => 'Данный email уже занят',
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
