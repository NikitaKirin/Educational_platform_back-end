<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Models\User;

class UserController extends Controller
{
    // Выводит список всех пользователей для администратора с пагинацией.
    public function index() {
        return new UserResourceCollection(User::paginate(5));
    }
}
