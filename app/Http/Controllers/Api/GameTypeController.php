<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameTypeResourceCollection;
use App\Models\GameType;

class GameTypeController extends Controller
{
    /**
     * Get games' types data fro constructor
     * Получить данные типов игры для конструктора
     */
    public function index() {
        return new GameTypeResourceCollection(GameType::all());
    }
}
