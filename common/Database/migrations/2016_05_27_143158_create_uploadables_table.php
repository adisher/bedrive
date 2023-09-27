<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUploadablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploadables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('upload_id');
            $table->unsignedInteger('uploadable_id');
            $table->string('uploadable_type', 60);

            $table->unique(['upload_id', 'uploadable_id', 'uploadable_type'], 'uploadable_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('uploadables');
    }
}
