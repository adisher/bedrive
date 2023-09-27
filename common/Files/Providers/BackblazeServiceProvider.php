<?php

namespace Common\Files\Providers;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\ServiceProvider;
use Storage;

class BackblazeServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('backblaze_s3', function ($app, $config) {
            $config[
                'endpoint'
            ] = "https://s3.{$config['region']}.backblazeb2.com";

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
