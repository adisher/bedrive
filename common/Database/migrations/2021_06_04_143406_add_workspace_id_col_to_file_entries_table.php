<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkspaceIdColToFileEntriesTable extends Migration
{
    public function up()
    {
        if ( ! Schema::hasColumn('file_entries', 'workspace_id')) {
            Schema::table('file_entries', function (Blueprint $table) {
                $table->integer('workspace_id')->unsigned()->nullable()->index();
            });
        }
    }

    public function down()
    {
        //
    }
}
