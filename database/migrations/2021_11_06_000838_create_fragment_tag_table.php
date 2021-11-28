<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFragmentTagTable extends Migration
{
    public function up() {
        Schema::create('fragment_tag', function ( Blueprint $table ) {
            $table->foreignId('fragment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('fragment_tag');
    }
}
