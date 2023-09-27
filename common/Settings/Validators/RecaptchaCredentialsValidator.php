<?php

namespace Common\Settings\Validators;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class RecaptchaCredentialsValidator
{
    const KEYS = ['recaptcha.site_key', 'recaptcha.secret_key'];

    public function fails($settings): array|false
    {
        try {
            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'response' => 'foo-bar',
                    'secret' => Arr::get($settings, 'recaptcha.secret_key'),
                ],
            );

            if (
                $response['success'] === false &&
                $response['error-codes'][0] !== 'invalid-input-response'
            ) {
                return [
                    'recaptcha_group' =>
                        Arr::get($response, 'error-codes')[0] ??
                        __('These credentials are not valid'),
                ];
            }
        } catch (Exception $e) {
            return $this->getErrorMessage($e);
        }

        return false;
    }

    private function getErrorMessage(Exception $e): array
    {
        return ['recaptcha_group' => $e->getMessage()];
    }
}
