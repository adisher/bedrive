<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('tags');

            if (array_key_exists('tags_name_type_unique', $indexesFound)) {
                $table->dropIndex('tags_name_type_unique');
            }
            $table->unique(['name', 'user_id', 'type']);
        });
    }

    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            //
        });
    }
};
