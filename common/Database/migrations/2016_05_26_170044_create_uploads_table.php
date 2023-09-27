<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUploadsTable extends Migration
{
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index();
            $table->string('file_name', 36)->unique();
            $table->string('file_size');
            $table->string('mime');
            $table->string('extension');
            $table->string('user_id')->index();
            $table->string('url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->timestamps();

            $table->collation = config('database.connections.mysql.collation');
            $table->charset = config('database.connections.mysql.charset');
        });
    }

    public function down()
    {
        Schema::drop('uploads');
    }
}
