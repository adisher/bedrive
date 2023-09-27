<?php

use Common\Settings\Setting;
use Illuminate\Database\Migrations\Migration;

class AddIdToAllMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $setting = Setting::where('name', 'menus')->first();
        if ( ! $setting) return;

        $transformed = collect(json_decode($setting['value'], true))->map(
            function ($menu) {
                if (!isset($menu['id'])) {
                    $menu['id'] = Str::random('6');
                }
                $menu['items'] = array_map(function ($item) {
                    if (!isset($item['id'])) {
                        $item['id'] = Str::random('6');
                    }
                    return $item;
                }, $menu['items']);
                return $menu;
            },
        );

        Setting::where('name', 'menus')->update(['value' => $transformed]);
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
