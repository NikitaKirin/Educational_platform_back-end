<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Profile\UpdateOwnAvatarRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class AvatarController extends Controller
{

    // Загрузить свой новый аватар. Функционал пользователя и администратора.
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function updateOwnAvatar( UpdateOwnAvatarRequest $request ): JsonResponse {
        $user = Auth::user();
        if ( isset($request->avatar) ) {
            if ( $user->hasAvatar($user) )
                $user->clearMediaCollection('user_avatars');
            $user->addMediaFromRequest('avatar')->toMediaCollection('user_avatars', 'user_avatars');
        }
        return response()->json([
            'message' => 'Аватар успешно загружен!',
            'avatar'  => $user->getAvatar($user),
        ], 200);
    }

    //Изменить аватар любого пользователя. Функционал администратора
    /*    public function updateSomeOneAvatar( UpdateSomeOneAvatarRequest $request, User $user ) {
            if ( isset($request->avatar) ) {
                if ( $user->hasAvatar() )
                    $user->clearMediaCollection('user_avatars');
                $user->addMediaFromRequest('avatar')->toMediaCollection('user_avatars', 'user_avatars');
            }
            return response()->json([
                'message' => 'Аватар успешно загружен!',
                'avatar'  => User::find($user->id)->getAvatar(),
            ], 200);
        }*/

    // Удалить свой аватар. Функционал пользователя и администратора.
    public function destroyOwnAvatar(): JsonResponse {
        $user = Auth::user();
        if ( $user->hasAvatar($user) ) {
            $user->clearMediaCollection('user_avatars');
            return response()->json([
                'message' => 'Аватар пользователя успешно удалён',
            ], 200);
        }
        return response()->json([
            'message' => 'Аватар профиля отсутствует',

        ], 400);
    }
}
