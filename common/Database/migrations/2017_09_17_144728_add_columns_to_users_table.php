<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('users', 'language')) return;

        Schema::table('users', function (Blueprint $table) {
            $table->string('language', 6)->nullable();
            $table->string('country', 40)->nullable();
            $table->string('timezone', 30)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('language');
            $table->dropColumn('country');
            $table->dropColumn('timezone');
        });
    }
}
