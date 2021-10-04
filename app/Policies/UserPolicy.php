<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    use HandlesAuthorization;

    public function before( User $user, $operation ) {
        if ( $user->role == 'admin' )
            return true;
    }

    public function __construct() {
        //
    }

    // Просмотр списка пользователей
    public function viewAny( User $user ) {
        return $user->role == 'admin' ? Response::allow() : Response::deny('Forbidden', 403);
    }

    // Просмотр профиля пользователя
    public function view( User $user, User $model ): bool {
        return false;
    }

    public function create( User $user ): bool {
        return false;
    }

    // Обновление данных профиля
    /*    public function update( User $user, User $model ): Response {
            return $user->id == $model->id ? Response::allow('Данные профиля успешно обновлены!') : Response::deny('Forbidden', 403);
        }*/

    public function delete( User $user, User $model ): bool {
        return false;
    }

    public function restore( User $user, User $model ): bool {
        return false;
    }

    public function forceDelete( User $user, User $model ): bool {
        return false;
    }
}
