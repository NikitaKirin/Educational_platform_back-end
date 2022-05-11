<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToGameTypesTable extends Migration
{
    public function up() {
        Schema::table('game_types', function ( Blueprint $table ) {
            $table->text('task')->default('Игровое задание');
        });
    }

    public function down() {
        Schema::table('game_types', function ( Blueprint $table ) {
            $table->dropColumn('task');
        });
    }
}
