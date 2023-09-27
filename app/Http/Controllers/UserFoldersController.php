<?php

namespace App\Http\Controllers;

use App\FileEntry;
use App\Folder;
use App\RootFolder;
use App\Services\Entries\SetPermissionsOnEntry;
use Common\Core\BaseController;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Http\JsonResponse;

class UserFoldersController extends BaseController
{
    public function __construct(private Folder $folder)
    {
    }

    public function index(int $userId): JsonResponse
    {
        $this->authorize('index', [FileEntry::class, null, $userId]);

        $query = $this->folder->where(
            'workspace_id',
            app(ActiveWorkspace::class)->id,
        );

        if (!app(ActiveWorkspace::class)->id) {
            $query->where('owner_id', $userId);
        }

        $folders = $query
            ->select(
                'file_entries.id',
                'name',
                'parent_id',
                'path',
                'type',
                'workspace_id',
            )
            ->with('users')
            ->orderByRaw('LENGTH(path)')
            ->limit(100)
            ->get();

        foreach ($folders as $key => $folder) {
            $folders[$key] = app(SetPermissionsOnEntry::class)->execute(
                $folder,
            );
        }

        return $this->success([
            'folders' => $folders,
            'rootFolder' => app(SetPermissionsOnEntry::class)->execute(
                new RootFolder(),
            ),
        ]);
    }
}
