<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comment_votes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vote_type', 10);
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('comment_id')->unsigned()->index();
            $table->string('user_ip', 20)->index();

            $table->unique(['user_id', 'comment_id']);
            $table->unique(['user_ip', 'comment_id']);
        });
    }

    public function down()
    {
        //
    }
};
