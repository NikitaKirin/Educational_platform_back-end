<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFragmentsTable extends Migration
{
    public function up() {
        Schema::create('fragments', function ( Blueprint $table ) {
            $table->id();
            $table->morphs('fragmentgable');
            $table->string('title');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('fragments');
    }
}
