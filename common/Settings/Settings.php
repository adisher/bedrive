<?php namespace Common\Settings;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Settings
{
    protected Collection $all;

    /**
     * Laravel config values that should be included with settings.
     * (display name for client => laravel config key)
     */
    protected array $configKeys = [
        'billing.stripe_public_key' => 'services.stripe.key',
        'billing.paypal.public_key' => 'services.paypal.client_id',
        'site.demo' => 'common.site.demo',
        'logging.sentry_public' => 'sentry.dsn',
        'i18n.default_localization' => 'app.locale',
        'billing.integrated' => 'common.site.billing_integrated',
        'workspaces.integrated' => 'common.site.workspaces_integrated',
        'notifications.integrated' => 'common.site.notifications_integrated',
        'notif.subs.integrated' => 'common.site.notif_subs_integrated',
        'api.integrated' => 'common.site.api_integrated',
        'branding.site_name' => 'app.name',
        'realtime.pusher_cluster' =>
            'broadcasting.connections.pusher.options.cluster',
        'realtime.pusher_key' => 'broadcasting.connections.pusher.key',
        'site.hide_docs_buttons' => 'common.site.hide_docs_buttons',
        'site.has_mobile_app' => 'common.site.has_mobile_app',
        'uploads.public_driver' => 'common.site.public_disk_driver',
        'uploads.uploads_driver' => 'common.site.uploads_disk_driver',
        'uploads.disable_tus' => 'common.site.uploads_disable_tus',
    ];

    /**
     * Settings that are json encoded in database.
     */
    protected array $jsonKeys = [
        'menus',
        'homepage.appearance',
        'uploads.allowed_extensions',
        'uploads.blocked_extensions',
        'cookie_notice.button',
        'registration.policies',
        'artistPage.tabs',
    ];

    protected array $privateKeys = [
        'recaptcha.secret_key',
        'google_safe_browsing_key',
    ];

    public function __construct()
    {
        $this->loadSettings();
    }

    public function all(bool $private = false): array
    {
        $all = $this->all;

        // filter out private (server-only) settings
        if (!$private) {
            $all = $all->filter(function ($setting) use ($private) {
                return !$setting['private'] &&
                    !in_array($setting['name'], $this->privateKeys);
            });
        }

        return $all->pluck('value', 'name')->toArray();
    }

    public function get(string|int $key, mixed $default = null): mixed
    {
        $value = $default;

        if ($setting = $this->find($key)) {
            $value = $setting['value'];
        }

        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Get a json setting by key and decode it.
     */
    public function getJson(string $key, array|null $default = null): array
    {
        $value = $this->get($key, $default);
        if (!is_string($value)) {
            return $value ?: [];
        }
        return json_decode($value, true);
    }

    /**
     * Get random setting value from fields that
     * have multiple values separated by newline.
     */
    public function getRandom(string $key, ?string $default = null): mixed
    {
        $key = $this->get($key, $default);
        $parts = explode("\n", $key);
        return $parts[array_rand($parts)];
    }

    /**
     * Check is setting with specified key exists.
     */
    public function has(string $key): bool
    {
        return !is_null($this->find($key));
    }

    /**
     * Set single setting. Does not persist in database.
     */
    public function set(string $key, mixed $value, bool $private = false): void
    {
        $this->all[$key] = [
            'name' => $key,
            'value' => $value,
            'private' => $private,
        ];
    }

    /**
     * Persist specified settings in database.
     */
    public function save(array $settings): void
    {
        $settings = $this->flatten($settings);
        foreach ($settings as $key => $value) {
            $setting = Setting::firstOrNew(['name' => $key]);
            $setting->value = !is_null($value) ? $value : '';
            $setting->save();
            $this->set($key, $setting->value);
        }

        Cache::forget('settings.public');
    }

    /**
     * Get all settings parsed from dot notation to assoc array. Also decodes JSON values.
     */
    public function getUnflattened(
        bool $private = false,
        array $settings = null,
    ): array {
        $dot = dot($settings ?? $this->all($private), true);

        foreach ($this->jsonKeys as $jsonKey) {
            if ($dot->has($jsonKey) && is_string($dot->get($jsonKey))) {
                $dot->set($jsonKey, json_decode($dot->get($jsonKey), true));
            }
        }

        return $dot->all();
    }

    /**
     * Flatten specified assoc array into dot array. (['billing.enable' => true])
     */
    protected function flatten(array $settings): array
    {
        $dot = dot($settings);

        // convert json setting values to json
        foreach ($this->jsonKeys as $jsonKey) {
            if ($dot->has($jsonKey) && !is_string($dot->get($jsonKey))) {
                $dot->set($jsonKey, json_encode($dot->get($jsonKey), true));
            }
        }

        // remove keys that were added from config files and are not stored in database
        $dotArray = $dot->delete(array_keys($this->configKeys))->flatten();
        // dot package leaves empty array as value for root element when deleting
        foreach ($dotArray as $key => $value) {
            if (is_array($value) && empty($value)) {
                unset($dotArray[$key]);
            }
        }

        return $dotArray;
    }

    /**
     * True if envato purchase code is required
     * for some functionality across the site.
     */
    public function envatoPurchaseCodeIsRequired(): bool
    {
        return $this->get('envato.enable') &&
            $this->get('envato.require_purchase_code');
    }

    private function find($key)
    {
        return Arr::get($this->all, $key);
    }

    /**
     * Load settings from database.
     */
    private function loadSettings(): void
    {
        $this->all = Cache::remember(
            'settings.public',
            Carbon::now()->addDay(),
            function () {
                try {
                    return Setting::select(['name', 'value', 'private'])
                        ->get()
                        ->mapWithKeys(function (Setting $setting) {
                            return [$setting->name => $setting->toArray()];
                        });
                } catch (Exception $e) {
                    return collect();
                }
            },
        );

        // add config keys that should be included
        foreach ($this->configKeys as $clientKey => $configKey) {
            $this->set($clientKey, config($configKey));
        }
    }
}
