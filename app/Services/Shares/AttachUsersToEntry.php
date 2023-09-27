<?php

namespace App\Services\Shares;

use App\Services\Shares\Traits\CreatesUserEntryPivotRecords;
use App\Services\Shares\Traits\GeneratesSharePermissions;
use App\User;
use Common\Files\Traits\LoadsAllChildEntries;
use DB;
use Illuminate\Database\Eloquent\Collection;

class AttachUsersToEntry
{
    use CreatesUserEntryPivotRecords,
        GeneratesSharePermissions,
        LoadsAllChildEntries;

    public function __construct(private User $user)
    {
    }

    /**
     * @return User[]|Collection
     */
    public function execute(array $emails, array $entries, array $permissions): array|\Illuminate\Database\Eloquent\Collection
    {
        $entryIds = collect($entries);

        // permissions on each user are expected
        $users = $this->user->whereIn('email', $emails)->get();

        $transformedUsers = $users->map(fn(User $user) => [
            'id' => $user->id,
            'permissions' => $this->generateSharePermissions($permissions),
        ]);

        $records = $this->createPivotRecords($transformedUsers, $entryIds);

        DB::table('file_entry_models')->insert($records->toArray());

        return $users;
    }
}
