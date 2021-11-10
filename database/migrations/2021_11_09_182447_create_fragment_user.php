<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFragmentUser extends Migration
{
    public function up() {
        Schema::create('fragment_user', function ( Blueprint $table ) {
            $table->id();
            $table->foreignId('fragment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('fragment_user');
    }
}
