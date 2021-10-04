<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Auth\RegisterRequest;
use App\Http\Requests\Api\User\Profile\UpdateOwnProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Выводит список всех пользователей для администратора с пагинацией.
    public function index(): UserResourceCollection {
        return new UserResourceCollection(User::paginate(5));
    }

    // Вывод страницы мой профиль
    public function me() {

        $user = new UserResource(Auth::user());
        if ( $user->role == 'admin' ) {
            return response()->json([
                'message' => 'Моя страница - администратор',
                'data'    => $user,
            ]);
        }

        return response()->json([
            'message' => 'Моя страница - пользователь',
            'data'    => $user,
        ]);

    }

    public function store( RegisterRequest $request ): \Illuminate\Http\JsonResponse {
        $user = User::create($request->all());
        return response()->json([
            'message' => 'Новый пользователь успешно создан!',
            'user'    => $user,
        ], 201);
    }

    public function show( User $user ) {

        /*        $user = User::find($request->user);
                if ( $user ) {
                    return response([
                        'message' => 'Страница пользователя',
                        'data'    => $user,
                    ], 200);
                }
                else {
                    return response()->json([
                        'message' => 'Такого пользователя не существует',
                    ], 404);
                }*/
        if ( $user ) {
            return response()->json([
                'user' => $user,
            ]);
        }
        return response()->json([
            'message' => 'Такого пользователя не существует',
        ], 404);
    }

    public function update( UpdateOwnProfileRequest $request ) {
        $user = User::find(Auth::user()->id);
        if ( $user->update($request->all()) ) {
            return response()->json([
                'message' => 'Данные успешно обновлены!',
                'user'    => new UserResource($user),
            ], 200);
        }
    }

}
