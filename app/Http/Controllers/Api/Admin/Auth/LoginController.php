<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    const LOGIN_REQUEST_RULES = [
        'email'    => 'required|email',
        'password' => 'string|required',
    ];

    const LOGIN_REQUEST_MESSAGES = [
        'required' => 'Данное поле обязательно для заполнения',
        'email'    => 'Вы ввели некорректный email',
    ];

    public function __invoke( Request $request ) {
        $validator = Validator::make($request->all(), self::LOGIN_REQUEST_RULES, self::LOGIN_REQUEST_MESSAGES);
        if ( $validator->fails() ) {
            $errors = $validator->errors();
            return response()->json(["messages" => $errors], 422);
        }

        if ( Auth::attempt($request->all()) && Auth::user()->role == 'admin' ) {
            $token = Auth::user()->createToken(config('app.name'));
            $token->token->save();
            return response()->json([
                'token_type' => 'Bearer',
                'token'      => $token->accessToken,
                'expires_at' => Carbon::parse($token->token->expires_at)->toDateTimeString(),
            ], 200);
        }

        return response()->json([
            'message' => 'You cannot sign with those credentials',
            'errors'  => 'Unauthorised',
        ], 401);
    }
}
