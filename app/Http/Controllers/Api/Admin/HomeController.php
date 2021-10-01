<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __invoke() {
        return [
            'messages' => 'Добро пожаловать!',
            'data'     => Auth::user(),
        ];
    }
}
