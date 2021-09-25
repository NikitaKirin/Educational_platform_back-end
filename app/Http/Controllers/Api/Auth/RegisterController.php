<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\Types\Self_;

class RegisterController extends Controller
{

    const REGISTER_REQUEST_RULES = [
        'surname'    => 'string|required',
        'name'       => 'string|required',
        'secondname' => 'string',
        'birthday'   => 'date',
        'role'       => 'required|in:creator,student',
        'email'      => 'required|email|unique:users',
        'password'   => 'required|string',
    ];

    const REGISTER_REQUEST_MESSAGES = [
        'string'       => 'Вы ввели недоступные символы',
        'required'     => 'Данное поле является обязательным для заполнения',
        'email.unique' => 'Данный email уже занят',
        'email'        => 'Введен неверный формат',
    ];

    public function __invoke( Request $request ) {
        $validator = Validator::make($request->all(), self::REGISTER_REQUEST_RULES, self::REGISTER_REQUEST_MESSAGES);
        if ( $validator->fails() ) {
            $errors = $validator->errors();
            return response()->json(["messages" => $errors], 422);
            //throw ValidationException::withMessages(["dates" => ['Невозможно удалить мероприятие с активными заявками']]);
        }

        $user = User::create($request->all());
        return response()->json([
            'message' => 'Вы успешно зарегистрированы!',
        ], 200);
    }
}
