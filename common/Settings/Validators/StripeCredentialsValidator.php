<?php

namespace Common\Settings\Validators;

use Common\Billing\Gateways\Stripe\Stripe;
use Config;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;

class StripeCredentialsValidator implements SettingsValidator
{
    const KEYS = ['stripe_key', 'stripe_secret'];

    public function fails($settings)
    {
        $this->setConfigDynamically($settings);

        // create gateway after setting config dynamically
        // so gateway uses new configuration
        $gateway = app(Stripe::class);

        try {
            $gateway->getAllPlans();
        } catch (ClientException $e) {
            return $this->getDefaultError();
        }
    }

    private function setConfigDynamically($settings)
    {
        foreach (self::KEYS as $key) {
            if (!Arr::has($settings, $key)) {
                continue;
            }

            // stripe_key => key
            $configKey = str_replace('stripe_', '', $key);
            Config::set("services.stripe.$configKey", $settings[$key]);
        }
    }

    private function getDefaultError(): array
    {
        return ['stripe_group' => 'These stripe credentials are not valid.'];
    }
}
