<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\throwException;

class HomeController extends Controller
{
    public function __invoke() {
        if ( Auth::user()->role == 'admin' ) {
            return [
                'messages' => 'Добро пожаловать!',
                'data'     => 'Главная страница администратора',
            ];
        }

        abort(403, 'Forbidden');
    }
}
