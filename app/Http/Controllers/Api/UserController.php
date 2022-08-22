<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Auth\RegisterRequest;
use App\Http\Requests\Api\Admin\Profile\UpdateSomeOneProfileRequest;
use App\Http\Requests\Api\User\Profile\UpdateOwnProfileRequest;
use App\Http\Resources\CreatorResource;
use App\Http\Resources\CreatorResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Mail\RegisterNewUserMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Orchid\Platform\Models\Role;

class UserController extends Controller
{
    // Выводит список всех пользователей для администратора с пагинацией.
    public function index(): UserResourceCollection {
        return new UserResourceCollection(User::orderBy('name')->paginate(10));
    }

    // Вывод страницы мой профиль
    public function me(): \Illuminate\Http\JsonResponse {

        $user = new UserResource(Auth::user());
        if ( $user->role == 'admin' ) {
            return response()->json([
                'message' => 'Моя страница - администратор',
                'user'    => $user,
            ]);
        }

        return response()->json([
            'message' => 'Моя страница - пользователь',
            'user'    => $user,
        ]);

    }

    // Создать нового пользователя - функционал администратора
    public function store( RegisterRequest $request ): \Illuminate\Http\JsonResponse {
        $user = User::create($request->all());
        Mail::to($request->email)
            ->send(new RegisterNewUserMail($request->name, $request->role, $request->email, $request->password));
        return response()->json([
            'message' => 'Новый пользователь успешно создан!',
            'user'    => new UserResource($user),
        ], 201);
    }

    // Выводит данные для страницы любого пользователя. Доступно только администраторам
    public function show( User $user ): \Illuminate\Http\JsonResponse {
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
        if ( $user->update($request->except(['role', 'avatar'])) ) {
            /*            if ( isset($request->avatar) ) {
                            if ( $user->hasAvatar() )
                                $user->clearMediaCollection('user_avatars');
                            $user->addMediaFromRequest('avatar')->toMediaCollection('user_avatars', 'user_avatars');
                        }*/
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

    // Заблокировать любого пользователя. Функционал администратора.
    public function block( User $user ): \Illuminate\Http\JsonResponse {

        if ( $user->blocked_at != null ) {
            return response()->json([
                'message' => 'Невозможно заблокировать уже заблокированного пользователя',
            ], 409);
        }
        elseif ( $user->update(['blocked_at' => Carbon::now()->toDateTimeString()]) )
            return response()->json([
                'message' => 'Пользователь успешно заблокирован',
                'user'    => new UserResource($user),
            ], 200);

        return response()->json([
            'Не удалось заблокировать пользователя',
        ], 409);
    }

    // Разблокировать любого пользователя. Функционал администратора.
    public function unblock( User $user ): \Illuminate\Http\JsonResponse {
        if ( $user->blocked_at == null )
            return response()->json([
                'message' => 'Невозможно разблокировать незаблокированного пользователя',
            ], 409);

        elseif ( $user->update(['blocked_at' => null]) )
            return response()->json([
                'message' => 'Пользователь успешно разблокирован',
                'user'    => new UserResource($user),
            ], 200);

        return response()->json([
            'Не удалось разблокировать пользователя',
        ], 409);
    }

    // Вывести список заблокированных пользователей. Функционал администратора.
    public function showBlocked(): UserResourceCollection {
        return new UserResourceCollection(User::where('blocked_at', '!=', null)->orderBy('name', 'asc')->paginate(10));
    }

    // Вывести список учителей. Функционал любого пользователя.
    public function teachersIndex( Request $request ): CreatorResourceCollection {
        $request->validate(['name' => 'string'], ['string' => 'Введены недопустимые символы']);
        $name = $request->input('name');
        return new CreatorResourceCollection(User::withCount(['fragments', 'lessons'])
                                                 ->whereHas('roles', function ( Builder $query ) {
                                                     return $query->where('slug', 'LIKE', 'creator');
                                                 })
                                                 ->where('blocked_at', null)
                                                 ->when($name, function ( $query ) use ( $name ) {
                                                     return $query->where('name', 'ILIKE', '%' . $name . '%');
                                                 })->orderBy('name')->paginate(10));
    }

    // Получить данные определенного учителя. Функционал любого пользователя.
    public function teacherShow( Request $request, User $user ) {
        if ( $user )
            return new CreatorResource($user->load('fragments')->loadCount('fragments'));
        return response(['message' => 'Такого пользователя не существует'], 404);
    }
}
