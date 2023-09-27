<?php

namespace Common\Core\Manifest;

use Common\Core\AppUrl;
use Common\Settings\Settings;

class BuildManifestFile
{
    public function __construct(protected Settings $settings)
    {
    }

    public function execute(): void
    {
        $primaryColor = config('common.themes.light.--be-primary');
        $bgColor = config('common.themes.light.--be-background');
        $replacements = [
            'DUMMY_NAME' => config('app.name'),
            'DUMMY_SHORT_NAME' => config('app.name'),
            'DUMMY_THEME_COLOR' => "rgb($primaryColor)",
            'DUMMY_BACKGROUND_COLOR' => "rgb($bgColor)",
            'DUMMY_START_URL' => app(AppUrl::class)->htmlBaseUri,
        ];

        @file_put_contents(
            public_path('manifest.json'),
            str_replace(
                array_keys($replacements),
                $replacements,
                file_get_contents(__DIR__ . '/manifest-example.json'),
            ),
        );
    }
}
