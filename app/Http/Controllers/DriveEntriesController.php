<?php

namespace App\Http\Controllers;

use App\FileEntry;
use App\Services\Entries\FetchDriveEntries;
use App\Services\Entries\SetPermissionsOnEntry;
use Common\Files\Controllers\FileEntriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriveEntriesController extends FileEntriesController
{
    public function __construct(Request $request, FileEntry $entry)
    {
        parent::__construct($request, $entry);
        $this->request = $request;
        $this->entry = $entry;
    }

    public function showModel($fileEntryId)
    {
        $fileEntry = FileEntry::findOrFail($fileEntryId);
        $this->authorize('show', $fileEntry);

        $fileEntry->load('users');
        app(SetPermissionsOnEntry::class)->execute($fileEntry);

        return $this->success(['fileEntry' => $fileEntry]);
    }

    public function index()
    {
        $this->middleware('auth');

        $params = $this->request->all();
        $params['userId'] = Auth::id();

        $this->authorize('index', [FileEntry::class, null, $params['userId']]);

        return app(FetchDriveEntries::class)->execute($params);
    }
}
