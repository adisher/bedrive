<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->string('plan_id')->index();
            $table
                ->string('gateway')
                ->default('none')
                ->index();
            $table
                ->string('gateway_id')
                ->nullable()
                ->unique()
                ->index();
            $table->integer('quantity')->default(1);
            $table->text('description')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamps();

            $table->collation = config('database.connections.mysql.collation');
            $table->charset = config('database.connections.mysql.charset');
        });
    }

    public function down()
    {
        Schema::drop('subscriptions');
    }
}
