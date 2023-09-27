<?php

namespace App\Services\Entries;

use App\FileEntry;
use App\RootFolder;
use Common\Database\Datasource\Datasource;
use Common\Database\Datasource\DatasourceFilters;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FetchDriveEntries
{
    private Builder|FileEntry $builder;

    private ?array $params = null;

    private ?bool $searching = null;

    private FileEntry|null $activeFolder = null;

    private DatasourceFilters|null $filters = null;

    private ?bool $sharedOnly = null;

    private ?bool $sharedByMe = null;

    public function __construct(
        private FileEntry $entry,
        private SetPermissionsOnEntry $setPermissionsOnEntry,
    ) {
    }

    public function execute(array $params): array
    {
        $params['perPage'] ??= 50;
        $this->params = $params;
        $this->builder = $this->entry->newQuery();
        $this->filters = new DatasourceFilters($params['filters'] ?? null);

        $starredOnly = $this->getBoolParam('starredOnly');
        $recentOnly = $this->getBoolParam('recentOnly');
        $this->sharedByMe = !!$this->filters->getAndRemove('sharedByMe');
        $this->sharedOnly =
            $this->getBoolParam('sharedOnly') ||
            !!$this->filters->getAndRemove('owner_id', '!=', Auth::id());
        $this->searching =
            Arr::get($params, 'query') || !$this->filters->empty();
        $entryIds = Arr::get($params, 'entryIds');
        $parentIds = Arr::get($params, 'parentIds');

        // folders should always be first
        $this->builder
            ->orderBy(DB::raw('type = "folder"'), 'desc')
            ->with('users', 'tags');

        $this->setActiveFolder($params);

        // fetch only entries that are children of specified parent,
        // in trash, show files/folders if their parent is not trashed
        if (
            !$this->showTrashedOnly() &&
            !$starredOnly &&
            !$recentOnly &&
            !$this->searching &&
            !$this->sharedOnly &&
            !$this->sharedByMe &&
            !$entryIds
        ) {
            if ($parentIds) {
                $this->builder->whereIn('parent_id', explode(',', $parentIds));
            } else {
                $this->builder->where(
                    'parent_id',
                    $this->activeFolder?->id ?: null,
                );
            }
        }

        $this->filterByUser();

        // load entries with ids matching [entryIds], but only if their parent id is not in [entryIds]
        if ($entryIds) {
            $entryIds = explode(',', $entryIds);
            $this->builder
                ->whereIn('file_entries.id', $entryIds)
                ->whereDoesntHave('parent', function ($query) use ($entryIds) {
                    $query->whereIn('file_entries.id', $entryIds);
                });
        }

        // fetch only entries that are in trash
        if ($this->showTrashedOnly()) {
            // only show entries that have no parent or whose parent is not trashed
            $this->builder->onlyTrashed()->whereRootOrParentNotTrashed();
        }

        // fetch only files, if we need recent entries
        if ($recentOnly) {
            $this->builder->where('type', '!=', 'folder');
        }

        // fetch only entries that are starred (favorited)
        if ($starredOnly) {
            $this->builder->onlyStarred();
        }

        // fetch only entries matching specified type (image, text, audio etc)
        if ($type = Arr::get($params, 'type')) {
            $this->builder->where('type', $type);
        }

        // make sure "public" uploads are not fetched
        $this->builder->where('public', 0);

        $datasource = (new Datasource(
            $this->builder,
            // prevent filtering by user id or workspace, it will be done here already
            Arr::except($params, ['userId', 'workspaceId']),
            $this->filters,
        ))->buildQuery();

        // order by name in case updated_at date is the same
        $orderCol = $this->builder->getQuery()->orders[0]['column'] ?? null;
        if (!is_string($orderCol) || $orderCol != 'name') {
            $this->builder->orderBy('name', 'asc');
        }

        $results = $datasource->paginate()->toArray();
        $results['data'] = array_map(
            fn($result) => $this->setPermissionsOnEntry->execute($result),
            $results['data'],
        );

        if ($this->activeFolder) {
            $results['folder'] = $this->activeFolder;
        }

        return $results;
    }

    protected function setActiveFolder(array $params)
    {
        if (array_key_exists('folderId', $params)) {
            if (!$params['folderId'] || is_numeric($params['folderId'])) {
                $folderId = (int) $params['folderId'];
                // it's a folder hash, need to decode it
            } else {
                $folderId = $this->entry->decodeHash($params['folderId']);
            }

            // if no folderId specified, assume root folder
            $activeFolder = !$folderId
                ? new RootFolder()
                : $this->entry->with('users')->find($folderId);
            if ($activeFolder) {
                $this->activeFolder = $this->setPermissionsOnEntry->execute(
                    $activeFolder,
                );
            }
        }
    }

    private function filterByUser()
    {
        $userId = $this->params['userId'];
        $workspaceId = app(ActiveWorkspace::class)->id ?? 0;

        if ($this->sharedByMe) {
            return $this->builder
                // when workspace is active, only show files shared from that workspace
                ->when(
                    $workspaceId,
                    fn() => $this->builder->where('workspace_id', $workspaceId),
                )
                ->sharedByUser($userId);
        }

        // shares page, get only entries user has access to, but did not upload
        if ($this->sharedOnly) {
            return $this->builder
                // when workspace is active, only show files shared from that workspace
                ->when(
                    $workspaceId,
                    fn() => $this->builder->where('workspace_id', $workspaceId),
                )
                ->sharedWithUserOnly($userId);
        }

        // filter by workspace.
        if ($workspaceId) {
            $canViewAllFiles = app(ActiveWorkspace::class)
                ->member($userId)
                ->hasPermission('files.view');
            if (!$canViewAllFiles) {
                $this->builder->where('owner_id', $userId);
            }

            if ($this->activeFolder?->id) {
                return $this->builder;
            } else {
                return $this->builder->where('workspace_id', $workspaceId);
            }
        } elseif (!$this->activeFolder?->id) {
            // If no workspace is active, and we have active folder, don't apply this filter as parent folder and children will always be in the same workspace. It will also allow listed contents of a shared folder that is in workspace.
            $this->builder->where('workspace_id', 0);
        }

        // listing children of specific folder or searching.
        // get all children of folder that user has access to
        if ($this->activeFolder?->id || $this->searching) {
            return $this->builder->whereUser($userId);
        }

        // root folder or other pages (recent, trash etc.)
        // get only entries that user has created
        return $this->builder->where('owner_id', $userId);
    }

    public function showTrashedOnly(): bool
    {
        if ($this->getBoolParam('deletedOnly')) {
            return true;
        }

        // check if "trashed" filter is active
        return !!Arr::first(
            $this->filters->getAll(),
            fn($filter) => $filter['key'] === 'deleted_at' &&
                $filter['operator'] === '!=' &&
                $filter['value'] === null,
        );
    }

    private function getBoolParam(string $name): bool
    {
        return filter_var(
            Arr::get($this->params, $name, false),
            FILTER_VALIDATE_BOOL,
        );
    }
}
