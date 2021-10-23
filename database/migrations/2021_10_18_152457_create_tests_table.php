<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestsTable extends Migration
{
    public function up() {
        Schema::create('tests', function ( Blueprint $table ) {
            $table->id();
            $table->json('content');
        });
    }

    public function down() {
        Schema::dropIfExists('tests');
    }
}
