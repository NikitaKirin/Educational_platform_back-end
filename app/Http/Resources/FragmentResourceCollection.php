<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** @see \App\Models\Fragment */
class FragmentResourceCollection extends ResourceCollection
{
    public static $wrap = 'fragments';

    public function toArray( $request ): array {
        $count = DB::table('fragments')->where('deleted_at', '=', null)
                   ->count(); // Запоминаем общее количество записей в таблице "fragments";
        return [
            'all_count' => $count,
            "fragments" => $this->collection,
        ];
    }
}
