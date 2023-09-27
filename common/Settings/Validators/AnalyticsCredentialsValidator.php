<?php

namespace Common\Settings\Validators;

use Common\Admin\Analytics\Actions\BuildGoogleAnalyticsReport;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class AnalyticsCredentialsValidator
{
    const KEYS = [
        'analytics_property_id',
        'analytics.tracking_code',
        'certificate',
    ];

    public function fails($settings): array|false
    {
        $this->setConfigDynamically($settings);

        try {
            app(BuildGoogleAnalyticsReport::class)->execute([]);
        } catch (Exception $e) {
            return [
                'analytics_group' => "Invalid credentials: {$e->getMessage()}",
            ];
        }

        return false;
    }

    private function setConfigDynamically(array $settings): void
    {
        if ($propertyId = Arr::get($settings, 'analytics_property_id')) {
            Config::set('services.google.analytics_property_id', $propertyId);
        }
    }
}
