<?php

use Common\Settings\Settings;
use Illuminate\Database\Migrations\Migration;

class MigrateLandingPageConfigTo20 extends Migration
{

    public function up()
    {
        $config = app(Settings::class)->get('homepage.appearance');
        if ($config) {
            $config = str_replace(
                'client\/assets\/images\/homepage\/inline-feature-1.png',
                'upload.svg',
                $config,
            );
            $config = str_replace(
                'client\/assets\/images\/homepage\/inline-feature-2.png',
                'web-devices.svg',
                $config,
            );
            $config = str_replace(
                'client\/assets\/images\/homepage\/inline-feature-3.png',
                'share.svg',
                $config,
            );
            app(Settings::class)->save([
                'homepage.appearance' => $config,
            ]);
        }
    }

    public function down()
    {
        //
    }
}
