<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToFragmentsTable extends Migration
{
    public function up() {
        Schema::table('fragments', function ( Blueprint $table ) {
            $table->string('age_limit')->nullable();
        });
    }

    public function down() {
        Schema::table('fragments', function ( Blueprint $table ) {
            $table->dropColumn('age_limit');
        });
    }
}
