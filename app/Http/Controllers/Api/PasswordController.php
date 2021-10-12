<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}
