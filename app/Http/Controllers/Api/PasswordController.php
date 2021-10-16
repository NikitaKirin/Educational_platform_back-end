<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Mockery\Generator\StringManipulation\Pass\Pass;

class PasswordController extends Controller
{
    public function passwordUpdate( Request $request ): \Illuminate\Http\JsonResponse {
        $validate_password = $request->validate([
            'password' => ['required', 'string'],
        ], [
            'required' => 'Это поле обязательно для заполнения',
            'string'   => 'Пароль содержит некорректные символы',
        ]);

        if ( Auth::user()->update(['password' => $request->input('password')]) ) {
            Auth::user()->refresh();
            return response()->json([
                'message' => 'Новый пароль успешно сохранен',
            ], 200);
        };

        return response()->json([
            'messages' => 'Не удалось обновить пароль',
        ]);
    }

    // Забыл пароль. Функционал пользователя и администратора.
    public function forgotPassword( Request $request ) {
        if ( $user = User::where('email', '=', $request->email)->get()->first() ) {
            $token = Password::broker()->createToken($user);
            Notification::send($user, new UserResetPasswordNotification($user->name, $token));
            return response()->json(['messages' => 'Ссылка для сброса пароля успешно отправлена']);
        }

        return response()->json([
            'message' => 'Не удалось отправить письмо',
        ], 409);


    }

    public function resetPassword( Request $request ) {
        $user = User::where('email', '=', $request->email)->get()->first();

        if ( Password::broker()->tokenExists($user, $request->token) ) {
            $user->update(['password' => $request->password]);
            Password::deleteToken($user);
            return response()->json([
                'messages' => 'Пароль успешно обновлен',
            ], 200);
        }
        return response('Не удалось обновить пароль', 409);
    }
}
