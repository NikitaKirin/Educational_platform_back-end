<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Models\User;

class UserController extends Controller
{
    // Выводит список всех пользователей для администратора с пагинацией.
    public function __invoke() {
        return new UserResourceCollection(User::paginate(5));
    }
}
