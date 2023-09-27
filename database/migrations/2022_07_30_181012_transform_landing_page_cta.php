<?php

use Common\Settings\Setting;
use Illuminate\Database\Migrations\Migration;

class TransformLandingPageCta extends Migration
{
    public function up()
    {
        $landing = app(Setting::class)
            ->where('name', 'homepage.appearance')
            ->first();
        if (!$landing) {
            return;
        }

        $landingConfig = json_decode($landing['value'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($landingConfig['actions'] as $key => $action) {
            if (is_string($action)) {
                $landingConfig['actions'][$key] = [
                    'label' => $action,
                    'type' => 'route',
                    'action' => '/register',
                ];
            }
        }

        $landing->update(['value' => json_encode($landingConfig, JSON_THROW_ON_ERROR)]);
    }

    public function down()
    {
        //
    }
}
