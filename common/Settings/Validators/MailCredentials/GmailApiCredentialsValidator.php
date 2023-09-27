<?php

namespace Common\Settings\Validators\MailCredentials;

use Arr;
use Common\Settings\Mail\GmailClient;
use Common\Settings\Settings;
use Common\Settings\Validators\SettingsValidator;
use Exception;

class GmailApiCredentialsValidator implements SettingsValidator
{
    const KEYS = ['mail.handler'];

    public function fails($settings): ?array
    {
        if (Arr::get($settings, 'mail.handler') === 'gmailApi') {
            if (!GmailClient::tokenExists()) {
                return [
                    'mail_group' => __('Gmail account needs to be connected.'),
                ];
            }

            if ($topicName = Arr::get($settings, 'gmail.incoming.topicName')) {
                app(Settings::class)->set(
                    'gmail.incoming.topicName',
                    $topicName,
                );
            }

            // init google pub sub
            try {
                app(GmailClient::class)->watch();
            } catch (Exception $e) {
                $decoded = json_decode($e->getMessage(), true);
                if (is_array($decoded) && isset($decoded['error']['message'])) {
                    return ['mail_group' => $decoded['error']['message']];
                } else {
                    return [
                        'mail_group' => $e->getMessage(),
                    ];
                }
            }
        }

        return null;
    }
}
