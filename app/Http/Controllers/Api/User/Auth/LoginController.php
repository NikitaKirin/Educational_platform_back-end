<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Auth\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Orchid\Platform\Models\Role;

class LoginController extends Controller
{
    public function __invoke( LoginRequest $request ): \Illuminate\Http\JsonResponse {

        if ( !Auth::attempt($request->all()) ) {
            return response()->json([
                'message' => 'Неверный логин или пароль',
                'errors'  => 'Unauthorised',
            ], 401);
        }

        $token = Auth::user()->createToken(config('app.name'));

        $token->token->save();

        return response()->json([
            'token_type' => 'Bearer',
            'token'      => $token->accessToken,
            'expires_at' => Carbon::parse($token->token->expires_at)->toDateTimeString(),
            'message'    => 'Добро пожаловать, ' . Auth::user()->name . '!',
            'user_id'    => Auth::id(),
            'user_role'  => collect(Auth::user()->getRoles())
                ->filter(fn( Role $role ) => ($role->slug === 'student' || $role->slug === 'creator'))
                ->first()
                ->slug,
        ], 200);
    }
}
