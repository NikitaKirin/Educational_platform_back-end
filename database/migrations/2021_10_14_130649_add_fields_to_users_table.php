<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    public function up() {
        Schema::table('users', function ( Blueprint $table ) {
            $table->timestamp('blocked_at')->nullable();
        });
    }

    public function down() {
        Schema::table('users', function ( Blueprint $table ) {
            //
        });
    }
}
