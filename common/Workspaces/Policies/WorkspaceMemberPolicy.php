<?php

namespace Common\Workspaces\Policies;

use App\User;
use Common\Core\Policies\BasePolicy;
use Common\Workspaces\Workspace;

class WorkspaceMemberPolicy extends BasePolicy
{
    public function store(
        User $currentUser,
        Workspace $workspace,
        $checkMemberCount = true
    ) {
        $member = $workspace->findMember($currentUser);

        if (!$member || !$member->hasPermission('workspace_members.invite')) {
            return false;
        }

        $owner =
            $currentUser->id === $workspace->owner_id
                ? $currentUser
                : $workspace->owner;
        $maxMemberCount = $owner->getRestrictionValue(
            'workspaces.create',
            'member_count',
        );

        if (!$checkMemberCount || !$maxMemberCount) {
            return true;
        }

        $currentMemberCount =
            $workspace->members()->count() + $workspace->invites->count();

        if ($currentMemberCount >= $maxMemberCount) {
            $message = __('policies.workspace_member_quota_exceeded');
            return $this->denyWithAction(
                $message,
                $owner->id === $currentUser->id ? $this->upgradeAction() : null,
            );
        }

        return true;
    }

    public function update(User $currentUser, Workspace $workspace)
    {
        if ($workspace->isOwner($currentUser)) {
            return true;
        } else {
            return $workspace
                ->findMember($currentUser)
                ->hasPermission('workspace_members.update');
        }
    }

    public function destroy(
        User $currentUser,
        Workspace $workspace,
        int $userId = null
    ) {
        if ($workspace->isOwner($currentUser)) {
            return true;
        } elseif ($currentUser->id === $userId) {
            // user is trying to delete their own membership, aka leaving workspace
            return true;
        } else {
            return $workspace
                ->findMember($currentUser)
                ->hasPermission('workspace_members.delete');
        }
    }
}
