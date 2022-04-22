<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToFragmentsTable extends Migration
{
    public function up() {
        Schema::table('fragments', function ( Blueprint $table ) {
            $table->foreignId('age_limit_id')->nullable()->constrained('age_limits')->nullOnDelete();
        });
    }

    public function down() {
        Schema::table('fragments', function ( Blueprint $table ) {
            $table->dropConstrainedForeignId('age_limit_id');
        });
    }
}
