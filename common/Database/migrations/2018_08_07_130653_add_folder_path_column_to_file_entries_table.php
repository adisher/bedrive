<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFolderPathColumnToFileEntriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('file_entries', 'path')) return;

        Schema::table('file_entries', function (Blueprint $table) {
            $table->string('path')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('file_entries', function (Blueprint $table) {
            $table->dropColumn('path');
        });
    }
}
