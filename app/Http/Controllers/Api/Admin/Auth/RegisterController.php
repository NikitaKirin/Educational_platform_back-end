<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Auth\RegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    // Контроллер для регистрации новых администраторов
    public function __invoke( RegisterRequest $request ) {
        $validated_fields = $request->all();
        $validated_fields['role'] = 'admin';

        $user = User::create($validated_fields);

        return response()->json([
            'message' => 'Новый администратор успешно зарегистрирован!',
            'user'    => $user,
        ], 201);
    }
}
