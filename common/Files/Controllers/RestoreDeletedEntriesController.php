<?php

namespace Common\Files\Controllers;

use Common\Core\BaseController;
use Common\Files\Actions\Deletion\RestoreEntries;
use Common\Files\FileEntry;
use Illuminate\Http\Request;

class RestoreDeletedEntriesController extends BaseController
{
    public function __construct(protected Request $request)
    {
    }

    public function restore(RestoreEntries $action)
    {
        $this->validate($this->request, [
            'entryIds' => 'required|array|exists:file_entries,id',
        ]);

        $entryIds = $this->request->get('entryIds');

        $this->authorize('destroy', [FileEntry::class, $entryIds]);

        $action->execute($entryIds);

        return $this->success();
    }
}
