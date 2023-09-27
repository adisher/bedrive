<?php

namespace Common\Settings\Mail;

use Common\Auth\Oauth;
use Common\Settings\Settings;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\File;
use Laravel\Socialite\Facades\Socialite;

class HandleConnectGmailOauthCallback
{
    public function execute(string $provider): ViewContract
    {
        $profile = Socialite::with('google')->user();

        File::ensureDirectoryExists(dirname(GmailClient::tokenPath()));
        File::put(
            GmailClient::tokenPath(),
            json_encode([
                'access_token' => $profile->token,
                'refresh_token' => $profile->refreshToken,
                'created' => now()->timestamp,
                'expires_in' => $profile->expiresIn,
                'email' => $profile->email,
            ]),
        );

        if (app(Settings::class)->get('mail.handler') === 'gmailApi') {
            app(GmailClient::class)->watch();
        }

        return app(Oauth::class)->getPopupResponse('SUCCESS', [
            'profile' => $profile,
        ]);
    }
}
