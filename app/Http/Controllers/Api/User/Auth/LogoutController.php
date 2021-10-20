<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke( Request $request ) {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Вы успешно вышли из системы!',
        ]);
    }
}
