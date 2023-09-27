<?php

use Common\Auth\Controllers\SocialAuthController;
use Common\Auth\Controllers\TwoFactorQrCodeController;
use Common\Billing\Invoices\InvoiceController;
use Common\Core\Controllers\HomeController;
use Common\Core\Controllers\UpdateController;
use Common\Csv\BaseCsvExportController;
use Common\Domains\CustomDomainController;
use Common\Settings\Mail\ConnectGmailAccountController;
use Common\Workspaces\Controllers\WorkspaceMembersController;

Route::group(['middleware' => 'web'], function () {
    // UPDATE
    Route::get('update', [UpdateController::class, 'show']);
    Route::get('secure/update', [UpdateController::class, 'show']);
    Route::post('secure/update/run', [UpdateController::class, 'update']);

    // make sure workspace version of login and register pages are shown on frontend
    Route::get('workspace/join/login', [HomeController::class, 'show']);
    Route::get('workspace/join/register', [HomeController::class, 'show']);

    // WORKSPACES
    Route::get('workspace/join/{workspaceInvite}', [
        WorkspaceMembersController::class,
        'join',
    ]);

    // CSV
    Route::get('csv/download/{csvExport}', [
        BaseCsvExportController::class,
        'download',
    ]);

    // INVOICES
    Route::get('billing/invoices/{uuid}', [InvoiceController::class, 'show']);

    // SOCIAL AUTH
    Route::group(['prefix' => 'secure'], function () {
        Route::get('auth/social/{provider}/connect', [
            SocialAuthController::class,
            'connect',
        ]);
        Route::get('auth/social/{provider}/login', [
            SocialAuthController::class,
            'login',
        ]);
        Route::post('auth/social/connect', [
            SocialAuthController::class,
            'connectWithPassword',
        ]);
        Route::get('auth/social/{provider}/retrieve-profile', [
            SocialAuthController::class,
            'retrieveProfile',
        ]);
        Route::get('auth/social/{provider}/callback', [
            SocialAuthController::class,
            'loginCallback',
        ]);
        Route::post('auth/social/{provider}/disconnect', [
            SocialAuthController::class,
            'disconnect',
        ]);
        Route::get('settings/mail/gmail/connect', [
            ConnectGmailAccountController::class,
            'connectGmail',
        ]);
    });

    // CUSTOM DOMAINS
    Route::group(
        ['prefix' => 'secure', 'middleware' => 'customDomainsEnabled'],
        function () {
            Route::post('custom-domain/authorize/{method}', [
                CustomDomainController::class,
                'authorizeCrupdate',
            ])->where('method', 'store|update');
            Route::post('custom-domain/validate/2BrM45vvfS/api', [
                CustomDomainController::class,
                'validateDomainApi',
            ]);
            Route::get('custom-domain/validate/2BrM45vvfS', [
                CustomDomainController::class,
                'validateDomain',
            ]);
        },
    );

    // TWO FACTOR AUTH
    Route::get('auth/user/two-factor/qr-code', [TwoFactorQrCodeController::class, 'show'])
      ->middleware(['auth', 'password.confirm']);

    // Laravel Auth routes with names so route('login') and similar calls don't error out
    Route::get('login', [HomeController::class, 'show'])->name('login');
    Route::get('register', [HomeController::class, 'show'])->name('register');
});
