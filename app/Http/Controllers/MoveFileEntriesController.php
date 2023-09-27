<?php

namespace App\Http\Controllers;

use App\FileEntry;
use App\Services\Entries\SetPermissionsOnEntry;
use Common\Core\BaseController;
use Common\Files\Events\FileEntriesMoved;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MoveFileEntriesController extends BaseController
{
    public function __construct(
        private Request $request,
        private FileEntry $entry,
    ) {
    }

    public function move(SetPermissionsOnEntry $setPermissions)
    {
        //should limit moves to 30 items (for now) for performance reasons
        $entryIds = collect($this->request->get('entryIds'))->take(30);
        $destinationId = $this->request->get('destinationId');

        $this->validate($this->request, [
            'entryIds' => 'required|array',
            'entryIds.*' => 'required|integer',
            'destinationId' => 'nullable|integer|exists:file_entries,id',
        ]);

        $newParent = $this->getNewParent($destinationId);
        if ($newParent && $newParent->type !== 'folder') {
            return $this->error(__('Destination must be a folder'));
        }

        $allEntries = collect([]);
        $entryIds
            ->chunk(30)
            ->each(function (Collection $entryIds) use (
                $destinationId,
                $setPermissions,
                $allEntries,
                $newParent,
            ) {
                $this->authorize('update', [
                    FileEntry::class,
                    $entryIds->toArray(),
                ]);

                $entries = $this->getEntries($entryIds);
                $entries = $this->removeInvalidEntries($entries, $newParent);

                // there was an issue with entries or parent, bail
                if ($entries->isEmpty()) {
                    return $this->error();
                }

                $this->updateParent($destinationId, $entries);
                $source = $entries->first()->parent_id;

                $entries->each(function (FileEntry $entry) use (
                    $newParent,
                    $destinationId,
                ) {
                    $entry->parent_id = $destinationId;
                    $oldPath = $entry->path;
                    $newPath = $newParent === null ? '' : $newParent->path;
                    $oldParent = last(explode('/', $oldPath));
                    $newPath .= "/$oldParent";
                    $this->entry->updatePaths($oldPath, $newPath);
                    $entry->path = $newPath;
                });

                event(
                    new FileEntriesMoved(
                        $entries->pluck('id')->toArray(),
                        $destinationId,
                        $source,
                    ),
                );

                $allEntries->merge(
                    $entries->map(
                        fn(FileEntry $entry) => $setPermissions->execute(
                            $entry,
                        ),
                    ),
                );
            });

        return $this->success([
            'entries' => $allEntries,
            'destination' => $newParent,
        ]);
    }

    /**
     * Make sure entries can't be moved into themselves or their children.
     */
    private function removeInvalidEntries(
        Collection $targets,
        ?FileEntry $destination,
    ) {
        if ($destination == null) {
            return $targets;
        }

        return $targets->filter(
            fn($entry) => $this->canMoveEntriesInto($entry, $destination),
        );
    }

    private function getNewParent(int|null $destination): FileEntry|null
    {
        if (!$destination) {
            return null;
        }
        return $this->entry->find($destination);
    }

    /**
     * @return Collection
     */
    private function getEntries(Collection $entryIds)
    {
        return $this->entry
            ->with('users')
            ->whereIn('id', $entryIds)
            ->get();
    }

    private function updateParent(?int $destination, Collection $entries)
    {
        $this->entry
            ->whereIn('id', $entries->pluck('id'))
            ->update(['parent_id' => $destination]);
    }

    private function canMoveEntriesInto(
        FileEntry $target,
        FileEntry $destination,
    ): bool {
        if (
            $destination->id === $target->parent_id ||
            // root folder check
            (!$target->parent_id && !$destination->id)
        ) {
            return false;
        }

        $destinationPath = explode('/', $destination->path ?: '');
        $targetPath = explode('/', $target->path ?: '');

        // destination is already in target
        return !collect($targetPath)->every(
            fn($part, $i) => ($destinationPath[$i] ?? null) === $part,
        );
    }
}
