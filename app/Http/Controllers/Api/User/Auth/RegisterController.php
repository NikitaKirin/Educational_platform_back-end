<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Auth\RegisterRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\ValidationException;
use Orchid\Platform\Models\Role;
use phpDocumentor\Reflection\Types\Self_;

class RegisterController extends Controller
{

    public function __invoke( RegisterRequest $request ) {
        if ( $request['birthday'] )
            $request['birthday'] = Carbon::parse($request['birthday'])->toDateString();
        $role = Role::all()->firstWhere('slug', $request->input('role'));
        $user = new User($request->except('role', 'password'));
        $user->fill(['password' => Hash::make($request->input('password'))]);
        $user->save();
        $user->addRole($role);
        if ( Auth::attempt([
            'email'    => $request->input('email'),
            "password" => $request->input('password'),
        ]) ) {
            $token = Auth::user()->createToken(config('app.name'));
            $token->token->save();
            return response()->json([
                'message'    => 'Вы успешно зарегистрированы!',
                'token_type' => 'Bearer',
                'token'      => $token->accessToken,
                'expires_at' => Carbon::parse($token->token->expires_at)->toDateTimeString(),
                'user_id'    => Auth::id(),
                'user_role'  => collect(Auth::user()->getRoles())
                    ->filter(fn( Role $role ) => ($role->slug === 'student' || $role->slug === 'creator'))
                    ->first()
                    ->slug,
            ], 200);
        }

        return response()->json(['error' => 'bad auth']);
    }
}
