<?php

namespace Common\Workspaces\Actions;

use App\User;
use Common\Workspaces\Notifications\WorkspaceInvitation;
use Common\Workspaces\WorkspaceInvite;
use Illuminate\Notifications\DatabaseNotification;

class DeleteInviteNotification
{
    public function execute(WorkspaceInvite $invite, User $user): void
    {
        $notifications = $user
            ->notifications()
            ->where('type', WorkspaceInvitation::class)
            ->limit(20)
            ->get();

        $notification = $notifications->first(function (
            DatabaseNotification $notification,
        ) use ($invite) {
            return $notification->data['inviteId'] === $invite->id;
        });

        if ($notification) {
            $notification->delete();
        }
    }
}
