<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('social_profiles', function (Blueprint $table) {
            $table
                ->text('access_token')
                ->nullable()
                ->change();
            $table
                ->text('refresh_token')
                ->nullable()
                ->change();
        });
    }
    
    public function down()
    {
        //
    }
};
