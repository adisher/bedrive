<?php

namespace Common\Workspaces\Actions;

use App\User;
use Common\Workspaces\WorkspaceInvite;
use Session;

class JoinWorkspace
{
    public function execute(WorkspaceInvite $invite, User $user)
    {
        if ($invite->email === $user->email) {
            $invite->workspace
                ->members()
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['role_id' => $invite->role_id],
                );

            app(DeleteInviteNotification::class)->execute($invite, $user);

            $invite->delete();
        }
        Session::remove('activeWorkspace');
    }
}
