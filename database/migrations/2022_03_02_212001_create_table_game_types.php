<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableGameTypes extends Migration
{
    public function up() {
        Schema::create('game_types', function ( Blueprint $table ) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('game_types');
    }
}
