<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid();
            $table->text('feature_list')->nullable();
            $table
                ->smallInteger('position')
                ->index()
                ->default(0);
            $table->boolean('recommended')->default(0);
            $table
                ->boolean('free')
                ->index()
                ->default(0);
            $table
                ->boolean('hidden')
                ->index()
                ->default(0);
            $table
                ->bigInteger('available_space')
                ->nullable()
                ->unsigned();
            $table->timestamps();
        });
    }

    public function down()
    {
        //
    }
}
