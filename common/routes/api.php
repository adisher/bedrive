<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Common\Admin\Analytics\AnalyticsController;
use Common\Admin\Appearance\Controllers\AppearanceController;
use Common\Admin\CacheController;
use Common\Admin\ImpersonateUserController;
use Common\Admin\Sitemap\SitemapController;
use Common\Auth\Controllers\AccessTokenController;
use Common\Auth\Controllers\BanController;
use Common\Auth\Controllers\MobileAuthController;
use Common\Auth\Controllers\SocialAuthController;
use Common\Auth\Controllers\UserAvatarController;
use Common\Auth\Controllers\UserController;
use Common\Auth\Controllers\UserSessionsController;
use Common\Auth\Roles\RolesController;
use Common\Billing\Gateways\Paypal\PaypalController;
use Common\Billing\Gateways\Stripe\StripeController;
use Common\Billing\Gateways\SyncProductsController;
use Common\Billing\Invoices\InvoiceController;
use Common\Billing\Products\ProductsController;
use Common\Billing\Subscriptions\SubscriptionsController;
use Common\Comments\Controllers\CommentableController;
use Common\Comments\Controllers\CommentController;
use Common\Core\Controllers\BootstrapController;
use Common\Core\Values\ValueListsController;
use Common\Csv\CommonCsvExportController;
use Common\Domains\CustomDomainController;
use Common\Files\Controllers\AddPreviewTokenController;
use Common\Files\Controllers\DownloadFileController;
use Common\Files\Controllers\FileEntriesController;
use Common\Files\Controllers\RestoreDeletedEntriesController;
use Common\Files\Controllers\ServerMaxUploadSizeController;
use Common\Files\S3\S3CorsController;
use Common\Files\S3\S3FileEntryController;
use Common\Files\S3\S3MultipartUploadController;
use Common\Files\S3\S3SimpleUploadController;
use Common\Files\Tus\TusFileEntryController;
use Common\Files\Tus\TusServer;
use Common\Localizations\LocalizationsController;
use Common\Localizations\UserLocaleController;
use Common\Notifications\NotificationController;
use Common\Notifications\NotificationSubscriptionsController;
use Common\Pages\ContactPageController;
use Common\Pages\CustomPageController;
use Common\Reports\ReportController;
use Common\Search\Controllers\NormalizedModelsController;
use Common\Search\Controllers\SearchSettingsController;
use Common\Settings\SettingsController;
use Common\Settings\Uploading\DropboxRefreshTokenController;
use Common\Tags\TagController;
use Common\Validation\RecaptchaController;
use Common\Votes\VoteController;
use Common\Workspaces\Controllers\WorkspaceController;
use Common\Workspaces\Controllers\WorkspaceInvitesController;
use Common\Workspaces\Controllers\WorkspaceMembersController;
use Common\Workspaces\UserWorkspacesController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;

// prettier-ignore
Route::group(['prefix' => 'v1'], function () {
    Route::group(['middleware' => ['optionalAuth:sanctum', 'verified']], function () {
        // FILE ENTRIES
        Route::get('file-entries/{fileEntry}/model', [FileEntriesController::class, 'showModel']);
        Route::get('file-entries/{fileEntry}', [FileEntriesController::class, 'show']);
        Route::get('file-entries', [FileEntriesController::class, 'index']);
        Route::post('file-entries/delete', [FileEntriesController::class, 'destroy']);
        Route::delete('file-entries/{entryIds}', [FileEntriesController::class, 'destroy']);
        Route::get('file-entries/download/{hashes}', [DownloadFileController::class, 'download']);
        Route::post('file-entries/{id}/add-preview-token', [AddPreviewTokenController::class, 'store']);
        Route::post('file-entries/restore', [RestoreDeletedEntriesController::class, 'restore']);

        // for swagger api docs
        Route::post('uploads', [FileEntriesController::class, 'store']);
        Route::post('file-entries', [FileEntriesController::class, 'store']);
        Route::put('file-entries/{id}', [FileEntriesController::class, 'update']);

        // S3 UPLOADS
        Route::post('s3/multipart/create', [S3MultipartUploadController::class, 'create']);
        Route::post('s3/multipart/batch-sign-part-urls', [S3MultipartUploadController::class, 'batchSignPartUrls']);
        Route::post('s3/multipart/get-uploaded-parts', [S3MultipartUploadController::class, 'getUploadedParts']);
        Route::post('s3/multipart/complete', [S3MultipartUploadController::class, 'complete']);
        Route::post('s3/multipart/abort', [S3MultipartUploadController::class, 'abort']);
        Route::post('s3/simple/presign', [S3SimpleUploadController::class, 'presignPost']);
        Route::post('s3/entries', [S3FileEntryController::class, 'store']);
        Route::post('s3/cors/upload', [S3CorsController::class, 'uploadCors']);

        // TUS UPLOADS
        Route::post('tus/entries', [TusFileEntryController::class, 'store']);
        Route::any('/tus/upload/{any?}', function () {
           return app(TusServer::class)->serve();
        })->where('any', '.*');

        // NOTIFICATIONS
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::delete('notifications/{ids}', [NotificationController::class, 'destroy']);
        Route::post('notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::get('notifications/{user}/subscriptions', [NotificationSubscriptionsController::class, 'index']);
        Route::put('notifications/{user}/subscriptions', [NotificationSubscriptionsController::class, 'update']);

        //ROLES
        Route::get('roles', [RolesController::class, 'index']);
        Route::get('roles/{role}', [RolesController::class, 'show']);
        Route::post('roles', [RolesController::class, 'store']);
        Route::put('roles/{id}', [RolesController::class, 'update']);
        Route::delete('roles/{id}', [RolesController::class, 'destroy']);
        Route::post('roles/{id}/add-users', [RolesController::class, 'addUsers']);
        Route::post('roles/{id}/remove-users', [RolesController::class, 'removeUsers']);
        Route::post('roles/csv/export', [CommonCsvExportController::class, 'exportRoles']);

        //USERS
        Route::get('users/{user}', [UserController::class, 'show'])->withoutMiddleware('verified');
        Route::apiResource('users', UserController::class)->except(['destroy', 'show']);
        Route::delete('users/{ids}', [UserController::class, 'destroy']);
        Route::post('access-tokens', [AccessTokenController::class, 'store']);
        Route::delete('access-tokens/{tokenId}', [AccessTokenController::class, 'destroy']);
        Route::post('users/csv/export', [CommonCsvExportController::class, 'exportUsers']);

        //USER AVATAR
        Route::post('users/{user}/avatar', [UserAvatarController::class, 'store']);
        Route::delete('users/{user}/avatar', [UserAvatarController::class, 'destroy']);

        //USER BANS
        Route::post('users/{user}/ban', [BanController::class, 'store']);
        Route::delete('users/{user}/unban', [BanController::class, 'destroy']);

        // USER SESSIONS
        Route::get('user-sessions', [UserSessionsController::class, 'index'])->middleware('auth');
        Route::post('user-sessions/logout-other', [UserSessionsController::class, 'LogoutOtherSessions'])->middleware(['auth', 'password.confirm']);

        // TAGS
        Route::get('tags', [TagController::class, 'index']);
        Route::post('tags', [TagController::class, 'store']);
        Route::put('tags/{id}', [TagController::class, 'update']);
        Route::delete('tags/{tagIds}', [TagController::class, 'destroy']);

        // WORKSPACES
        Route::apiResource('workspace', WorkspaceController::class);
        Route::get('me/workspaces', [UserWorkspacesController::class, 'index']);
        Route::delete('workspace/{workspace}/member/{userId}', [WorkspaceMembersController::class, 'destroy']);
        Route::post('workspace/{workspace}/invite', [WorkspaceInvitesController::class, 'store']);
        Route::post('workspace/{workspace}/{workspaceInvite}/resend', [WorkspaceInvitesController::class, 'resend']);
        Route::post('workspace/{workspace}/member/{memberId}/change-role', [WorkspaceMembersController::class, 'changeRole']);
        Route::post('workspace/{workspace}/invite/{inviteId}/change-role', [WorkspaceInvitesController::class, 'changeRole']);
        Route::delete('workspace/invite/{workspaceInvite}', [WorkspaceInvitesController::class, 'destroy']);
        Route::get('workspace/join/{workspaceInvite}', [WorkspaceMembersController::class, 'join']);

        // SETTINGS
        Route::get('settings', [SettingsController::class, 'index']);
        Route::post('settings', [SettingsController::class, 'persist']);
        Route::post('settings/uploading/dropbox-refresh-token', [DropboxRefreshTokenController::class, 'generate']);

        // SITEMAP
        Route::post('sitemap/generate', [SitemapController::class, 'generate']);

        // APPEARANCE EDITOR
        Route::post('admin/appearance', [AppearanceController::class, 'save']);
        Route::get('admin/appearance/values', [AppearanceController::class, 'getValues']);

        // CUSTOM PAGES
        Route::apiResource('custom-pages', CustomPageController::class);

        // COMMENTS
        Route::apiResource('comment', CommentController::class);
        Route::post('comment/restore', [CommentController::class, 'restore']);
        Route::get('commentable/comments', [CommentableController::class, 'index']);

        // VOTES
        Route::post('vote', [VoteController::class, 'store']);

        // REPORTS
        Route::post('report', [ReportController::class, 'store']);
        Route::delete('report/{modelType}/{modelId}', [ReportController::class, 'destroy']);

        // CONTACT PAGE
        Route::post('contact-page', [ContactPageController::class, 'sendMessage']);

        // SEARCH
        Route::get('normalized-models/{modelType}', [NormalizedModelsController::class, 'index']);
        Route::get('normalized-models/{modelType}/{modelId}', [NormalizedModelsController::class, 'show']);

        // PRODUCTS
        Route::apiResource('billing/products', ProductsController::class);
        Route::post('billing/products/sync', [SyncProductsController::class, 'syncProducts']);

        // SUBSCRIPTIONS
        Route::get('billing/subscriptions', [SubscriptionsController::class, 'index']);
        Route::post('billing/subscriptions', [SubscriptionsController::class, 'store']);
        Route::post('billing/subscriptions/{subscription}/cancel', [SubscriptionsController::class, 'cancel']);
        Route::put('billing/subscriptions/{subscription}', [SubscriptionsController::class, 'update']);
        Route::post('billing/subscriptions/{subscription}/resume', [SubscriptionsController::class, 'resume']);
        Route::post('billing/subscriptions/{subscription}/change-plan', [SubscriptionsController::class, 'changePlan']);
        Route::post('billing/stripe/create-partial-subscription', [StripeController::class, 'createPartialSubscription']);
        Route::post('billing/stripe/create-setup-intent', [StripeController::class, 'createSetupIntent']);
        Route::post('billing/stripe/change-default-payment-method', [StripeController::class, 'changeDefaultPaymentMethod']);
        Route::post('billing/stripe/store-subscription-details-locally', [StripeController::class, 'storeSubscriptionDetailsLocally']);
        Route::post('billing/paypal/store-subscription-details-locally', [PaypalController::class, 'storeSubscriptionDetailsLocally']);

        // INVOICES
        Route::get('billing/invoices', [InvoiceController::class, 'index']);

        // CUSTOM DOMAINS
        Route::apiResource('custom-domain', CustomDomainController::class)->middleware('customDomainsEnabled');

        // ADMIN
        Route::get('uploads/server-max-file-size', [ServerMaxUploadSizeController::class, 'index']);
        Route::get('admin/reports', [AnalyticsController::class, 'report']);
        Route::get('admin/reports/header', [AnalyticsController::class, 'headerReport']);
        Route::get('admin/reports/sessions', [AnalyticsController::class, 'sessionsReport']);
        Route::post('cache/flush', [CacheController::class, 'flush']);
        Route::post('admin/users/impersonate/{id}', [ImpersonateUserController::class, 'impersonate']);
        Route::get('admin/search/models', [SearchSettingsController::class, 'getSearchableModels']);
        Route::post('admin/search/import', [SearchSettingsController::class, 'import']);

        // LOCALIZATIONS
        Route::post('localizations', [LocalizationsController::class, 'store']);
        Route::put('localizations/{langCode}', [LocalizationsController::class, 'update']);
        Route::delete('localizations/{id}', [LocalizationsController::class, 'destroy']);
        Route::post('users/me/locale', [UserLocaleController::class, 'update']);
        Route::get('localizations', [LocalizationsController::class, 'index']);
        Route::get('localizations/{idOrLangCode}', [LocalizationsController::class, 'show']);

        // VALUE LISTS
        Route::get('value-lists/{names}', [ValueListsController::class, 'index'])->withoutMiddleware('verified');

        // RECAPTCHA
        Route::post('recaptcha/verify', [RecaptchaController::class, 'verify']);

        // BOOTSTRAP
        Route::get('bootstrap-data', [BootstrapController::class, 'getBootstrapData']);
        Route::get('remote-config/mobile', [BootstrapController::class, 'getMobileBootstrapData']);
    });

    // Mobile app auth
    $limiter = config('fortify.limiters.login');
    Route::post('auth/login', [MobileAuthController::class, 'login'])->middleware(array_filter([
        $limiter ? 'throttle:'.$limiter : null,
    ]));
    Route::post('auth/register', [MobileAuthController::class, 'register']);
    Route::get('auth/social/{provider}/callback', [SocialAuthController::class, 'loginCallback']);
    Route::post('auth/password/email', [PasswordResetLinkController::class, 'store']);
});
