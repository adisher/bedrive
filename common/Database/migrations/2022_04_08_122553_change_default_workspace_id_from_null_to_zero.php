<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use const App\Providers\WORKSPACED_RESOURCES;

class ChangeDefaultWorkspaceIdFromNullToZero extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!defined(WORKSPACED_RESOURCES::class)) {
            return;
        }

        foreach (WORKSPACED_RESOURCES as $resource) {
            $table = app($resource)->getTable();
            if (!Schema::hasColumn($table, 'workspace_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table
                        ->integer('workspace_id')
                        ->unsigned()
                        ->default(0)
                        ->index();
                });
            }
        }

        foreach (WORKSPACED_RESOURCES as $resource) {
            app($resource)
                ->whereNull('workspace_id')
                ->update(['workspace_id' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
