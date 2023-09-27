<?php

use App\Http\Controllers\DriveEntriesController;
use App\Http\Controllers\DuplicateEntriesController;
use App\Http\Controllers\EntrySyncInfoController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\FileEntryTagsController;
use App\Http\Controllers\FolderPathController;
use App\Http\Controllers\FoldersController;
use App\Http\Controllers\MoveFileEntriesController;
use App\Http\Controllers\ShareableLinkPasswordController;
use App\Http\Controllers\ShareableLinksController;
use App\Http\Controllers\SharesController;
use App\Http\Controllers\SpaceUsageController;
use App\Http\Controllers\StarredEntriesController;
use App\Http\Controllers\UserFoldersController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['prefix' => 'v1', 'middleware' => ['optionalAuth:sanctum', 'verified']],
    function () {
        // SHARING
        Route::post('file-entries/{id}/share', [
            SharesController::class,
            'addUsers',
        ]);
        Route::post('file-entries/{id}/unshare', [
            SharesController::class,
            'removeUser',
        ]);
        Route::put('file-entries/{id}/change-permissions', [
            SharesController::class,
            'changePermissions',
        ]);

        // SHAREABLE LINK
        Route::get('file-entries/{id}/shareable-link', [
            ShareableLinksController::class,
            'show',
        ]);
        Route::post('file-entries/{id}/shareable-link', [
            ShareableLinksController::class,
            'store',
        ]);
        Route::put('file-entries/{id}/shareable-link', [
            ShareableLinksController::class,
            'update',
        ]);
        Route::delete('file-entries/{id}/shareable-link', [
            ShareableLinksController::class,
            'destroy',
        ]);
        Route::post('shareable-links/{linkId}/import', [
            SharesController::class,
            'addCurrentUser',
        ]);

        // ENTRIES
        Route::get('drive/file-entries/{fileEntry}/model', [
            DriveEntriesController::class,
            'showModel',
        ]);
        Route::get('drive/file-entries', [
            DriveEntriesController::class,
            'index',
        ]);
        Route::post('file-entries/sync-info', [
            EntrySyncInfoController::class,
            'index',
        ]);
        Route::post('file-entries/move', [
            MoveFileEntriesController::class,
            'move',
        ]);
        Route::post('file-entries/duplicate', [
            DuplicateEntriesController::class,
            'duplicate',
        ]);
        Route::post('file-entries/{fileEntry}/sync-tags', [
            FileEntryTagsController::class,
            'sync',
        ]);

        // FOLDERS
        Route::post('folders', [FoldersController::class, 'store']);
        Route::get('users/{userId}/folders', [
            UserFoldersController::class,
            'index',
        ]);
        Route::get('folders/{hash}/path', [
            FolderPathController::class,
            'show',
        ]);

        // Tags/Labels
        Route::get('file-entry-tags', [
            FileEntryTagsController::class,
            'index',
        ]);
        Route::post('file-entries/star', [
            StarredEntriesController::class,
            'add',
        ]);
        Route::post('file-entries/unstar', [
            StarredEntriesController::class,
            'remove',
        ]);

        //SPACE USAGE
        Route::get('user/space-usage', [SpaceUsageController::class, 'index']);

        //SHAREABLE LINKS PREVIEW (NO AUTH NEEDED)
        Route::get('shareable-links/{hash}', [
            ShareableLinksController::class,
            'show',
        ]);
        Route::post('shareable-links/{linkHash}/check-password', [
            ShareableLinkPasswordController::class,
            'check',
        ]);

        // FCM TOKENS
        Route::post('fcm-token', [FcmTokenController::class, 'store']);
    },
);
