<?php

namespace Common\Settings\Uploading;

use Common\Core\BaseController;
use Common\Settings\DotEnvEditor;
use Illuminate\Support\Facades\Http;

class DropboxRefreshTokenController extends BaseController
{
    public function generate()
    {
        $payload = $this->validate(request(), [
            'app_key' => 'required|string',
            'app_secret' => 'required|string',
            'access_code' => 'required|string',
        ]);

        $response = Http::asForm()->post(
            "https://{$payload['app_key']}:{$payload['app_secret']}@api.dropbox.com/oauth2/token",
            [
                'grant_type' => 'authorization_code',
                'code' => $payload['access_code'],
            ],
        );

        if (isset($response['refresh_token'])) {
            app(DotEnvEditor::class)->write([
                'STORAGE_DROPBOX_REFRESH_TOKEN' => $response['refresh_token'],
            ]);
            return $this->success([
                'refreshToken' => $response['refresh_token'],
            ]);
        }

        return $this->error($response['error_description'] ?? null);
    }
}
