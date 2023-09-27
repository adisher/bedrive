<?php

namespace App\Http\Controllers;

use App;
use App\FileEntry;
use App\Notifications\FileEntrySharedNotif;
use App\Services\Shares\AttachUsersToEntry;
use App\Services\Shares\DetachUsersFromEntries;
use App\Services\Shares\GetUsersWithAccessToEntry;
use App\ShareableLink;
use App\User;
use Common\Core\BaseController;
use Common\Files\Traits\LoadsAllChildEntries;
use Common\Settings\Settings;
use Common\Validation\Validators\EmailsAreValid;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SharesController extends BaseController
{
    use LoadsAllChildEntries;

    public function __construct(
        private Request $request,
        private Settings $settings
    ) {
    }

    /**
     * Import entry into current user's drive using specified shareable link.
     */
    public function addCurrentUser(
        int $linkId,
        AttachUsersToEntry $action,
        ShareableLink $linkModel
    ): JsonResponse {
        /* @var ShareableLink $link */
        $link = $linkModel->with('entry')->findOrFail($linkId);

        $this->authorize('show', [$link->entry, $link]);

        $permissions = [
            'view' => true,
            'edit' => $link->allow_edit,
            'download' => $link->allow_download,
        ];

        $action->execute(
            [$this->request->user()->email],
            [$link->entry_id],
            $permissions,
        );

        $users = app(GetUsersWithAccessToEntry::class)->execute(
            $link->entry_id,
        );

        return $this->success(['users' => $users]);
    }

    public function addUsers(
        int $entryId,
        AttachUsersToEntry $action
    ): JsonResponse {
        $shareeEmails = $this->request->get('emails');

        $this->authorize('update', [FileEntry::class, [$entryId]]);

        $emails = $this->request->get('emails', []);

        $messages = [];
        foreach ($emails as $key => $email) {
            $messages["emails.$key"] = $email;
        }

        $this->validate(
            $this->request,
            [
                'emails' => ['required', 'min:1', new EmailsAreValid()],
                'permissions' => 'required|array',
            ],
            [],
            $messages,
        );

        $sharees = $action->execute(
            $shareeEmails,
            [$entryId],
            $this->request->get('permissions'),
        );

        if ($this->settings->get('drive.send_share_notification')) {
            try {
                Notification::send(
                    $sharees,
                    new FileEntrySharedNotif([$entryId], Auth::user()),
                );
            } catch (Exception) {
                //
            }
        }

        $users = app(GetUsersWithAccessToEntry::class)->execute($entryId);

        return $this->success(['users' => $users]);
    }

    public function changePermissions(int $entryId)
    {
        $this->request->validate([
            'permissions' => 'required|array',
            'userId' => 'required|int',
        ]);

        $this->authorize('update', [FileEntry::class, [$entryId]]);

        DB::table('file_entry_models')
            ->where('model_id', $this->request->get('userId'))
            ->where('model_type', User::class)
            ->whereIn(
                'file_entry_id',
                $this->loadChildEntries([$entryId])->pluck('id'),
            )
            ->update([
                'permissions' => json_encode(
                    $this->request->get('permissions'),
                    JSON_THROW_ON_ERROR,
                ),
            ]);

        $users = app(GetUsersWithAccessToEntry::class)->execute($entryId);

        return $this->success(['users' => $users]);
    }

    public function removeUser(
        string $entryIds,
        DetachUsersFromEntries $action
    ): JsonResponse {
        $userId =
            $this->request->get('userId') === 'me'
                ? Auth::id()
                : (int) $this->request->get('userId');
        $entryIds = explode(',', $entryIds);

        // there's no need to authorize if user is
        // trying to remove himself from the entry
        if ($userId !== Auth::id()) {
            $this->authorize('update', [FileEntry::class, $entryIds]);
        }

        $action->execute(collect($entryIds), collect([$userId]));

        $users = app(GetUsersWithAccessToEntry::class)->execute(
            head($entryIds),
        );

        return $this->success(['users' => $users]);
    }
}
