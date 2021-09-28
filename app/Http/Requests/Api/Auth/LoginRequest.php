<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules() {
        return [
            'email'    => 'required|email',
            'password' => 'string|required',
        ];
    }

    public function messages() {
        return [
            'required' => 'Данное поле обязательно для заполнения',
            'email'    => 'Вы ввели некорректный email',
        ];
    }

/*    public function withValidator( $validator ) {
        if ( $validator->fails() ) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }
    }*/

    public function authorize() {
        return true;
    }
}
