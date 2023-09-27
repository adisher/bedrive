<?php

namespace App\Services\Shares;

use App\User;
use App\FileEntry;
use Illuminate\Database\Eloquent\Collection;

class GetUsersWithAccessToEntry
{
    public function __construct(private FileEntry $entry)
    {
    }

    /**
     * @param int $entryId
     * @return Collection|User[]
     */
    public function execute($entryId): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->entry->with('users')->find($entryId)->users;
    }
}
