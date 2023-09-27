<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('user_id')
                ->nullable()
                ->index();
        });
    }

    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
