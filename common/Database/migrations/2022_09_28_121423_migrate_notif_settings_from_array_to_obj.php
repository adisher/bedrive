<?php

use Common\Notifications\NotificationSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        NotificationSubscription::chunkById(50, function (Collection $subs) {
            $subs->each(function (NotificationSubscription $sub) {
                $subChannels = $sub->channels;
                if (is_numeric(array_keys($subChannels)[0])) {
                    $sub->channels = collect($subChannels)->mapWithKeys(
                        fn($channel) => [$channel => true],
                    );
                    $sub->save();
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('array_to_obj', function (Blueprint $table) {
            //
        });
    }
};
