<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFragmentLesson extends Migration
{
    public function up() {
        Schema::create('fragment_lesson', function ( Blueprint $table ) {
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fragment_id')->constrained()->cascadeOnDelete();
            $table->integer('order');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('fragment_lesson');
    }
}
