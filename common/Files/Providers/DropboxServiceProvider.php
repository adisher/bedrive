<?php

namespace Common\Files\Providers;

use Common\Settings\DotEnvEditor;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\Dropbox\RefreshableTokenProvider;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Storage;

class DropboxServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('dropbox', function ($app, $config) {
            $config = array_merge($config, ['case_sensitive' => false]);

            $tokenProvider = new class ($config) implements
                RefreshableTokenProvider
            {
                protected string|null $token;

                public function __construct(protected array $config)
                {
                    $this->token = $this->config['access_token'];
                }

                public function refresh(ClientException $exception): bool
                {
                    $response = Http::asForm()->post(
                        "https://{$this->config['app_key']}:{$this->config['app_secret']}@api.dropbox.com/oauth2/token",
                        [
                            'grant_type' => 'refresh_token',
                            'refresh_token' => $this->config['refresh_token'],
                        ],
                    );
                    $response->throw();

                    app(DotEnvEditor::class)->write([
                        'STORAGE_DROPBOX_ACCESS_TOKEN' =>
                            $response['access_token'],
                    ]);
                    $this->token = $response['access_token'] ?? null;
                    return $this->token ?: false;
                }

                public function getToken(): string
                {
                    return $this->token ?? '';
                }
            };

            $adapter = new DropboxAdapter(new DropboxClient($tokenProvider));
            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config,
            );
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
