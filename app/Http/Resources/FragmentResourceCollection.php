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
        $fragments_title = null;
        if ( $request->is('api/my-fragments*') )
            $all_count = DB::table('fragments')->where('user_id', Auth::id())->where('deleted_at', null)->count();
        elseif ( $request->is('api/fragments/like*') )
            $all_count = Auth::user()->favouriteFragments()->count();
        elseif ( $request->is('api/fragments*') )
            $all_count = DB::table('fragments')->where('deleted_at', '=', null)->count();
        elseif ( $request->route()->named('fragments.teacher.index') )
            $all_count = $request->user->fragments()->count();
        elseif ( $request->route()->named('lesson.show') ) {
            $all_count = $request->lesson->fragments()->count();
            $fragments_title = $request->lesson->fragments()->orderBy('order')->get(['title', 'fragmentgable_type']);
        }


        return [
            'all_count'       => $all_count,
            'lesson_title'    => $this->when($request->routeIs('lesson.show'), function () use ( $request ) {
                return $request->lesson->title;
            }),
            'user_id'         => $this->when($request->routeIs('lesson.show'), function () use ( $request ) {
                return $request->lesson->user_id;
            }),
            'fragments_title' => $this->when(isset($fragments_title), $fragments_title),
            'data'            => $this->collection,
        ];
    }
}
