<?php

namespace Common\Files\Actions\Deletion;

use Common\Files\Events\FileEntriesDeleted;
use Common\Files\FileEntry;
use Common\Files\Traits\LoadsAllChildEntries;
use Illuminate\Support\Collection;

class SoftDeleteEntries
{
    use LoadsAllChildEntries;

    public function __construct(protected FileEntry $entry)
    {
    }

    public function execute(Collection|array $entryIds): void
    {
        collect($entryIds)
            ->chunk(50)
            ->each(function ($ids) {
                $entries = $this->entry
                    ->withTrashed()
                    ->whereIn('id', $ids)
                    ->get();
                $this->delete($entries);
            });
    }

    /**
     * Move specified entries to "trash".
     */
    protected function delete(Collection|array $entries): void
    {
        $entries = $this->loadChildEntries($entries);
        $this->entry->whereIn('id', $entries->pluck('id'))->delete();
        event(new FileEntriesDeleted($entries->pluck('id')->toArray(), false));
    }
}
