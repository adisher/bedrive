<?php

namespace App\Http\Controllers;

use Auth;
use Common\Core\BaseController;
use Common\Files\FileEntry;
use Illuminate\Http\Request;

class EntrySyncInfoController extends BaseController
{
    public function __construct(protected Request $request, protected FileEntry $entry)
    {
    }

    public function index()
    {
        $userId = $this->request->get('userId', Auth::user()->id);
        $this->authorize('index', [FileEntry::class, null, $userId]);

        $this->validate($this->request, [
            'fileNames' => 'required|array',
            'fileNames.*' => 'required|string',
        ]);

        $entries = Auth::user()->entries()
            ->whereIn('file_name', $this->request->get('fileNames'))
            ->select(['file_name', 'file_entries.updated_at', 'file_entries.id', 'type'])
            ->get()
            ->map(fn(FileEntry $entry) => $entry->setAppends([]));

        return $this->success(['entries' => $entries]);
    }
}
