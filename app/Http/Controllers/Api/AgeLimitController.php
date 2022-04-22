<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgeLimitResourceCollection;
use App\Models\AgeLimit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AgeLimitController extends Controller
{
    /**
     * Handle the incoming request.
     * Возвращает список всех возрастных цензоров
     * @param Request $request
     * @return AgeLimitResourceCollection
     */
    public function __invoke( Request $request ) {
        return new AgeLimitResourceCollection(AgeLimit::all());
    }
}
