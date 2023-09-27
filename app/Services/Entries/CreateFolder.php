<?php

namespace App\Services\Entries;

use App\Folder;
use Arr;
use Common\Workspaces\ActiveWorkspace;

class CreateFolder
{
    public function __construct(private Folder $folder)
    {
    }

    public function execute(array $data): Folder
    {
        $ownerId = $data['ownerId'];
        $parentId = Arr::get($data, 'parentId');
        $folderName = $data['name'];

        $exists = $this->folder
            ->where('parent_id', $parentId)
            ->where('workspace_id', app(ActiveWorkspace::class)->id)
            ->where('name', $data['name'])
            ->where('type', 'folder')
            ->where('owner_id', $ownerId)
            ->first();

        if (!is_null($exists)) {
            throw new FolderExistsException();
        }

        $folder = $this->folder->create([
            'name' => $folderName,
            'file_name' => $folderName,
            'parent_id' => $parentId,
            'owner_id' => $ownerId,
        ]);

        $folder->generatePath();

        $folder->users()->attach($ownerId, ['owner' => true]);

        return $folder;
    }
}
