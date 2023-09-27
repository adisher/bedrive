<?php

namespace App\Http\Controllers;

use App\FileEntry;
use App\Folder;
use App\Services\Entries\CreateFolder;
use App\Services\Entries\FolderExistsException;
use App\Services\Entries\SetPermissionsOnEntry;
use Auth;
use Common\Core\BaseController;
use Common\Files\Events\FileEntryCreated;
use Illuminate\Http\Request;

class FoldersController extends BaseController
{
    public function __construct(
        private Folder $folder,
        private Request $request
    ) {
    }

    public function show()
    {
        $folder = null;
        if ($this->request->has('hash')) {
            $folder = $this->folder
                ->with('users')
                ->whereHash($this->request->get('hash'))
                ->firstOrFail();
        }

        $this->authorize('show', $folder);

        return $this->success(['folder' => $folder]);
    }

    public function store()
    {
        $name = $this->request->get('name');
        $parentId = $this->request->get('parentId');

        $this->validate($this->request, [
            'name' => 'required|string|min:3',
            'parentId' => 'nullable|integer|exists:file_entries,id',
        ]);

        $this->authorize('store', [FileEntry::class, $parentId]);

        try {
            $folder = app(CreateFolder::class)->execute([
                'name' => $name,
                'parentId' => $parentId,
                'ownerId' => Auth::id(),
            ]);
        } catch (FolderExistsException) {
            return $this->error('', [
                'name' => __('Folder with same name already exists.'),
            ]);
        }

        event(new FileEntryCreated($folder));

        return $this->success([
            'folder' => app(SetPermissionsOnEntry::class)->execute(
                $folder->load('users'),
            ),
        ]);
    }
}
