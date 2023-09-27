<?php

namespace Common\Files\Actions\Deletion;

use Common\Files\Events\FileEntriesRestored;
use Illuminate\Support\Collection;

class RestoreEntries extends SoftDeleteEntries
{
    public function execute(Collection|array $entryIds): void
    {
        $entries = $this->entry
            ->onlyTrashed()
            ->whereIn('id', $entryIds)
            ->get();
        $entries = $this->loadChildEntries($entries, true);

        $this->entry->whereIn('id', $entries->pluck('id'))->restore();

        event(new FileEntriesRestored($entries->pluck('id')->toArray()));
    }
}
