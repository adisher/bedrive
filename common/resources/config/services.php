<?php

return [
    'ftp' => [
        'host' => env('STORAGE_FTP_HOST'),
        'username' => env('STORAGE_FTP_USERNAME'),
        'password' => env('STORAGE_FTP_PASSWORD'),
        'port' => (int) env('STORAGE_FTP_PORT', 21),
        'passive' => env('STORAGE_FTP_PASSIVE'),
        'ssl' => env('STORAGE_FTP_SSL'),
    ],

    'dropbox' => [
        'app_key' => env('STORAGE_DROPBOX_APP_KEY'),
        'app_secret' => env('STORAGE_DROPBOX_APP_SECRET'),
        'refresh_token' => env('STORAGE_DROPBOX_REFRESH_TOKEN'),
        'access_token' => env('STORAGE_DROPBOX_ACCESS_TOKEN'),
    ],

    'backblaze_s3' => [
        'key' => env('STORAGE_BACKBLAZE_KEY'),
        'secret' => env('STORAGE_BACKBLAZE_SECRET'),
        'bucket' => env('STORAGE_BACKBLAZE_BUCKET'),
        'region' => env('STORAGE_BACKBLAZE_REGION'),
    ],

    's3' => [
        'key' => env('STORAGE_S3_KEY'),
        'secret' => env('STORAGE_S3_SECRET'),
        'region' => env('STORAGE_S3_REGION'),
        'bucket' => env('STORAGE_S3_BUCKET'),
        'endpoint' => env('STORAGE_S3_ENDPOINT'),
        'use_path_style_endpoint' => env('STORAGE_S3_USE_PATH_STYLE_ENDPOINT', false),
    ],

    'digitalocean_s3' => [
        'key' => env('STORAGE_DIGITALOCEAN_KEY'),
        'secret' => env('STORAGE_DIGITALOCEAN_SECRET'),
        'region' => env('STORAGE_DIGITALOCEAN_REGION'),
        'bucket' => env('STORAGE_DIGITALOCEAN_BUCKET'),
    ],

    'rackspace' => [
        'username' => env('STORAGE_RACKSPACE_USERNAME'),
        'key' => env('STORAGE_RACKSPACE_KEY'),
        'container' => env('STORAGE_RACKSPACE_CONTAINER'),
        'endpoint' => 'https://identity.api.rackspacecloud.com/v2.0/',
        'region' => env('STORAGE_RACKSPACE_REGION', 'IAD'),
        'url_type' => 'publicURL',
    ],

    /**
     * Billing credentials
     */

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        'product_id' => env('PAYPAL_PRODUCT_ID'),
        'product_name' => env('PAYPAL_PRODUCT_NAME'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /**
     * Socialite login credentials
     */

    'google' => [
        'client_id' => env('GOOGLE_ID'),
        'client_secret' => env('GOOGLE_SECRET'),
        'redirect' => env('APP_URL') . '/secure/auth/social/google/callback',
        'analytics_property_id' => env('ANALYTICS_PROPERTY_ID'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_ID'),
        'client_secret' => env('TWITTER_SECRET'),
        'redirect' => env('APP_URL') . '/secure/auth/social/twitter/callback',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_ID'),
        'client_secret' => env('FACEBOOK_SECRET'),
        'redirect' => env('APP_URL') . '/secure/auth/social/facebook/callback',
    ],

    /**
     * Other
     */

    'slack' => [
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
    ],
];
