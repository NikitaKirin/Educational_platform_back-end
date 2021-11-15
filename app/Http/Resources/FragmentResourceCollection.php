<?php

namespace App\Http\Resources;

use App\Models\Fragment;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** @see \App\Models\Fragment */
class FragmentResourceCollection extends ResourceCollection
{
    public static $wrap = "fragments";

    public function toArray( $request ): array {
        /*        return [
                    'all_count'      => $this->when($request->is('api/fragments*'), function () {
                        return DB::table('fragments')->where('deleted_at', '=', null)
                                 ->count(); // Запоминаем общее количество записей в таблице "fragments";
                    }),
                    'all_user_count' => $this->when($request->is('api/my-fragments*'), function () {
                        return DB::table('fragments')->where('user_id', Auth::id())->where('deleted_at', null)
                                 ->count();// Запоминаем общее количество записей в таблице "fragments" текущего пользователя;
                    }),
                    "fragments"      => $this->collection,
                ];*/
        $all_count = 0;
        if ( $request->is('api/my-fragments*') )
            $all_count = DB::table('fragments')->where('user_id', Auth::id())->where('deleted_at', null)->count();
        elseif ( $request->is('api/fragments/like*') )
            $all_count = Auth::user()->favouriteFragments()->count();
        elseif ( $request->is('api/fragments*') )
            $all_count = DB::table('fragments')->where('deleted_at', '=', null)->count();
        elseif ( $request->route()->named('fragments.teacher.index') )
            $all_count = $request->user->fragments()->count();

        return [
            'all_count' => $all_count,
            'data'      => $this->collection,
        ];
    }
}
