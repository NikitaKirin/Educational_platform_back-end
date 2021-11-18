<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLessons extends Migration
{
    public function up() {
        Schema::create('lessons', function ( Blueprint $table ) {
            $table->id();
            $table->string('title');
            $table->string('annotation');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('lessons');
    }
}
