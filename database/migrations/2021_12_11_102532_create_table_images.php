<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableImages extends Migration
{
    public function up() {
        Schema::create('images', function ( Blueprint $table ) {
            $table->id();
            $table->string('content');
        });
    }

    public function down() {
        Schema::dropIfExists('images');
    }
}
