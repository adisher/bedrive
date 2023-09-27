<?php

namespace App\Listeners;

use App\FileEntry;
use Common\Files\Actions\Deletion\DeleteEntries;
use Common\Workspaces\Events\WorkspaceDeleted;

class HandleDeletedWorkspace
{
    public function handle(WorkspaceDeleted $e)
    {
        // delete workspace owner entries
        $entryIds = FileEntry::where('workspace_id', $e->workspaceId)
            ->where('owner_id', $e->ownerId)
            ->pluck('id');
        app(DeleteEntries::class)->execute([
            'entryIds' => $entryIds,
            false,
        ]);

        // transfer other workspace member files to their personal workspace
        FileEntry::where('workspace_id', $e->workspaceId)
            ->where('owner_id', '!=', $e->ownerId)
            ->update([
                'workspace_id' => null,
            ]);
    }
}
