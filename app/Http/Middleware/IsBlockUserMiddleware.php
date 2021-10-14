<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsBlockUserMiddleware
{
    public function handle( Request $request, Closure $next ) {
        if ( Auth::user()->blocked_at != null )
            return response()->json([
                'message'    => 'Forbidden. Пользователь заблокирован',
                'blocked_at' => Auth::user()->blocked_at,
            ], 403);
        else
            return $next($request);
    }
}
