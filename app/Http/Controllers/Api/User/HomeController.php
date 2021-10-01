<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __invoke() {
        return response()->json([
            'message' => 'Добро пожаловать',
            'data' => Auth::user()
        ]);
    }
}
