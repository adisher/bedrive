<?php

namespace Common\Billing\Gateways\Paypal;

use Carbon\Carbon;
use Common\Settings\Settings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait InteractsWithPaypalRestApi
{
    protected string|null $accessToken = null;
    protected Carbon|null $tokenExpires = null;

    public function paypal(): PendingRequest
    {
        $baseUrl = app(Settings::class)->get('billing.paypal_test_mode')
            ? 'https://api-m.sandbox.paypal.com/v1'
            : 'https://api-m.paypal.com/v1';

        if (
            !$this->accessToken ||
            $this->tokenExpires->lessThan(Carbon::now())
        ) {
            $clientId = config('services.paypal.client_id');
            $secret = config('services.paypal.secret');
            $response = Http::withBasicAuth($clientId, $secret)
                ->asForm()
                ->post("$baseUrl/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);
            if (!$response->successful()) {
                $response->throw();
            }
            $this->accessToken = $response['access_token'];
            $this->tokenExpires = Carbon::now()->addSeconds(
                $response['expires_in'],
            );
        }

        return Http::withToken($this->accessToken)->baseUrl($baseUrl);
    }
}
