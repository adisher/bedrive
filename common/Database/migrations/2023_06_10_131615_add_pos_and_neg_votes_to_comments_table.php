<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('upvotes')
                ->default(0)
                ->index();
            $table
                ->unsignedBigInteger('downvotes')
                ->default(0)
                ->index();
            $table
                ->unsignedBigInteger('reports_count')
                ->default(0)
                ->index();
        });
    }

    public function down()
    {
        //
    }
};
