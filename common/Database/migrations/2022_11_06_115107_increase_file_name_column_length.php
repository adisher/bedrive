<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('file_entries', function (Blueprint $table) {
            $table->string('file_name', 50)->change();
        });
    }
    public function down()
    {
        //
    }
};
