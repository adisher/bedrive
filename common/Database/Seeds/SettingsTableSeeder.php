<?php namespace Common\Database\Seeds;

use Carbon\Carbon;
use Common\Settings\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SettingsTableSeeder extends Seeder
{
    public function __construct(protected Setting $setting)
    {
    }

    public function run()
    {
        $defaultSettings = config('common.default-settings');

        $names = [];

        $defaultSettings = array_map(function ($setting) use (&$names) {
            $names[] = $setting['name'];

            $setting['created_at'] = Carbon::now();
            $setting['updated_at'] = Carbon::now();

            //make sure all settings have "private" field to
            //avoid db errors due to different column count
            if (!array_key_exists('private', $setting)) {
                $setting['private'] = 0;
            }

            // cast booleans to string as "insert"
            // method will not use Setting model setters
            if ($setting['value'] === true) {
                $setting['value'] = 'true';
            } elseif ($setting['value'] === false) {
                $setting['value'] = 'false';
            }
            $setting['value'] = (string) $setting['value'];

            return $setting;
        }, $defaultSettings);

        $existing = $this->setting->whereIn('name', $names)->pluck('name');

        //only insert settings that don't already exist in database
        $new = array_filter($defaultSettings, function ($setting) use (
            $existing,
        ) {
            return !$existing->contains($setting['name']);
        });

        $this->setting->insert($new);

        $this->mergeMenusSetting($defaultSettings);
    }

    /**
     * Merge existing menus setting json with new one.
     */
    private function mergeMenusSetting(array $defaultSettings): void
    {
        $existing =
            json_decode(
                $this->setting->where('name', 'menus')->first()->value,
                true,
            ) ?? [];
        $new = json_decode(
            Arr::first(
                $defaultSettings,
                fn($value) => $value['name'] === 'menus',
            )['value'],
            true,
        );

        foreach ($new as $menu) {
            $alreadyHas = Arr::first($existing, function ($value) use ($menu) {
                return $value['name'] === $menu['name'];
            });

            foreach ($menu['items'] as $index => $item) {
                $menu['items'][$index]['order'] = $index;
            }

            if (!$alreadyHas) {
                $existing[] = $menu;
            }
        }

        $this->setting
            ->where('name', 'menus')
            ->update(['value' => json_encode($existing)]);
    }
}
