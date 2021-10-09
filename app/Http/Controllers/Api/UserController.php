<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Auth\RegisterRequest;
use App\Http\Requests\Api\Admin\Profile\UpdateSomeOneProfileRequest;
use App\Http\Requests\Api\User\Profile\UpdateOwnProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Models\User;
use Carbon\Carbon;
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

    // Создать нового пользователя - функционал администратора
    public function store( RegisterRequest $request ): \Illuminate\Http\JsonResponse {
        $user = User::create($request->all());
        return response()->json([
            'message' => 'Новый пользователь успешно создан!',
            'user'    => $user,
        ], 201);
    }

    // Выводит данные для страницы любого пользователя. Доступно только администраторам
    public function show( User $user ) {
        if ( $user ) {
            return response()->json([
                'user' => $user,
            ]);
        }
        return response()->json([
            'message' => 'Такого пользователя не существует',
        ], 404);
    }

    // Изменить данные своего профиля: пользователи и администраторы
    public function update( UpdateOwnProfileRequest $request ) {

        $user = User::find(Auth::user()->id);
        if ( $user->update($request->all()) ) {
            if ( isset($request->avatar) ) {
                if($user->hasAvatar())
                    $user->clearMediaCollection('user_avatars');
                $user->addMediaFromRequest('avatar')->toMediaCollection('user_avatars', 'user_avatars');
            }
            return response()->json([
                'message' => 'Данные успешно обновлены!',
                'user'    => new UserResource($user),
            ], 200);
        }
    }

    // Изменить данные профиля любого пользователя. Функционал администратора.
    public function updateSomeOneProfile( UpdateSomeOneProfileRequest $request, User $user ) {

        if ( $user->update($request->all()) ) {
            return response()->json([
                'message' => 'Данные успешно обновлены!',
                'user'    => new UserResource($user),
            ], 200);
        }
    }

}
