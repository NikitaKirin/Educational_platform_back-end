<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLessonUser extends Migration
{
    public function up() {
        Schema::create('lesson_user', function ( Blueprint $table ) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('lesson_user');
    }
}
