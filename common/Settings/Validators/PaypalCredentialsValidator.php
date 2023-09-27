<?php

namespace Common\Settings\Validators;

use Common\Billing\Gateways\Paypal\Paypal;
use Common\Settings\Settings;
use Config;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;

class PaypalCredentialsValidator implements SettingsValidator
{
    const KEYS = [
        'paypal_client_id',
        'paypal_secret',
        'paypal_webhook_id',
        'billing.paypal_test_mode',
    ];

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function fails($settings)
    {
        $this->setConfigDynamically($settings);

        // create gateway after setting config dynamically
        // so gateway uses new configuration

        try {
            $response = app(Paypal::class)
                ->paypal()
                ->get('payments/billing-plans');
            if (!$response->successful()) {
                return $this->getErrorMessage($response->body());
            }
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

            if ($key === 'billing.paypal_test_mode') {
                $this->settings->set(
                    'billing.paypal_test_mode',
                    $settings[$key],
                );
            } else {
                // paypal_client_id => client_id
                $configKey = str_replace('paypal_', '', $key);
                Config::set("services.paypal.$configKey", $settings[$key]);
            }
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function getErrorMessage($data)
    {
        $message = Arr::get($data, 'message');
        if ($data['name'] === 'AUTHENTICATION_FAILURE') {
            return [
                'paypal_group' =>
                    'Paypal Client ID or Paypal Secret is invalid.',
            ];
        } elseif ($message) {
            $infoLink = Arr::get($data, 'information_link');
            return ['paypal_group' => "$message. $infoLink"];
        } else {
            return $this->getDefaultError();
        }
    }

    private function getDefaultError()
    {
        return ['paypal_group' => 'These paypal credentials are not valid.'];
    }
}
