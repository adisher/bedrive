<?php

namespace Common\Files\Actions;

use App\User;
use Common\Files\Events\FileEntryCreated;
use Common\Files\FileEntry;
use Common\Files\FileEntryPayload;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CreateFileEntry
{
    public function __construct(protected FileEntry $entry)
    {
    }

    public function execute(FileEntryPayload $payload): FileEntry
    {
        $ownerId = $payload->ownerId;
        $data = [
            'name' => $payload->clientName,
            'file_name' => $payload->filename,
            'mime' => $payload->clientMime,
            'file_size' => $payload->size,
            'parent_id' => $payload->parentId,
            'disk_prefix' => $payload->diskPrefix,
            'type' => $payload->type,
            'extension' => $payload->clientExtension,
            'public' => $payload->public,
            'workspace_id' => $payload->workspaceId,
            'owner_id' => $ownerId,
        ];

        $entries = new Collection();

        // uploading a folder
        if ($payload->relativePath && !$payload->public) {
            $path = $this->createPath(
                $payload->relativePath,
                $ownerId,
                $data['parent_id'],
            );
            $parent = $path['allParents']->last();
            if ($path['allParents']->isNotEmpty()) {
                $entries = $entries->merge($path['allParents']);
                $data['parent_id'] = $parent->id;
            }
        }

        $fileEntry = $this->entry->create($data);

        if (!$payload->public) {
            $fileEntry->generatePath();
        }

        $entries = $entries->push($fileEntry);

        $entryIds = $entries
            ->mapWithKeys(function ($entry) {
                return [$entry->id => ['owner' => 1]];
            })
            ->toArray();

        User::find($ownerId)
            ->entries()
            ->syncWithoutDetaching($entryIds);

        if (isset($path['newlyCreated'])) {
            $path['newlyCreated']->each(function (FileEntry $entry) use (
                $payload
            ) {
                // make sure new folder gets attached to all
                // users who have access to the parent folder
                event(new FileEntryCreated($entry));
            });
        }

        if (isset($parent) && $parent) {
            $fileEntry->setRelation('parent', $parent);
        } else {
            $fileEntry->load('parent');
        }

        $entries->load('users');

        event(new FileEntryCreated($fileEntry));

        return $fileEntry;
    }

    private function createPath(
        string $path,
        int $userId,
        int $parentId = null
    ): array {
        $newlyCreated = collect();
        $dirname = dirname($path);
        // remove file name from path and split into folder names
        $path = collect(
            explode('/', $dirname === '.' ? $path : $dirname),
        )->filter();
        if ($path->isEmpty()) {
            return $path->toArray();
        }

        $allParents = $path->reduce(function ($parents, $name) use (
            $parentId,
            $userId,
            $newlyCreated
        ) {
            if (!$parents) {
                $parents = collect();
            }
            $parent = $parents->last();

            $values = [
                'type' => 'folder',
                'name' => $name,
                // file name is limited to 36 chars in database, make sure we match that if we get very long file names
                'file_name' => Str::limit($name, 36, ''),
                'parent_id' => $parent ? $parent->id : $parentId,
                'workspace_id' => app(ActiveWorkspace::class)->id ?? 0,
            ];

            // check if user already has a folder with that name and parent
            $folder = $this->entry
                ->where($values)
                ->whereUser($userId)
                ->first();

            if (!$folder) {
                $values['owner_id'] = $userId;
                $folder = $this->entry->create($values);
                $folder->generatePath();
                $newlyCreated->push($folder);
            }

            return $parents->push($folder);
        });

        return ['allParents' => $allParents, 'newlyCreated' => $newlyCreated];
    }
}
