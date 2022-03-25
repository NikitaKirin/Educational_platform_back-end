<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    public function up() {
        Schema::create('games', function ( Blueprint $table ) {
            $table->id();
            $table->foreignId('game_type_id')->constrained('game_types')->cascadeOnDelete();
            $table->json('content');
        });
    }

    public function down() {
        Schema::dropIfExists('games');
    }
}
