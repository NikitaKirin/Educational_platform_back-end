<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Profile\UpdateSomeOneAvatarRequest;
use App\Http\Requests\Api\User\Profile\UpdateOwnAvatarRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvatarController extends Controller
{

    // Загрузить свой новый аватар. Функционал пользователя и администратора.
    public function updateOwnAvatar( UpdateOwnAvatarRequest $request ) {
        $user = Auth::user();
        if ( isset($request->avatar) ) {
            if ( $user->hasAvatar() )
                $user->clearMediaCollection('user_avatars');
            $user->addMediaFromRequest('avatar')->toMediaCollection('user_avatars', 'user_avatars');
            $avatar = $user->getAvatar();
        }
        return response()->json([
            'message' => 'Аватар успешно загружен!',
            'avatar'  => $avatar,
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
    public function destroyOwnAvatar() {
        $user = Auth::user();
        if ( $user->hasAvatar() ) {
            $user->clearMediaCollection('user_avatars');
            return response()->json([
                'message' => 'Аватар пользователя успешно удалён',
            ], 200);
        }
    }
}
