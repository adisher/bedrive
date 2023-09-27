<?php

namespace Common\Files\Providers;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class DigitalOceanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Storage::extend('digitalocean_s3', function ($app, $config) {
            $config[
                'endpoint'
            ] = "https://{$config['region']}.digitaloceanspaces.com";

            return app(FilesystemManager::class)->createS3Driver($config);
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
