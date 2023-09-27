<?php

namespace App\Policies;

use App\Services\Links\ValidatesLinkPassword;
use App\ShareableLink;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShareableLinkPolicy
{
    use HandlesAuthorization, ValidatesLinkPassword;

    public function show(User $user, ShareableLink $link)
    {
        if ($link->user_id === $user->id) {
            return true;
        }

        return $user->hasPermission('links.view') &&
            $this->passwordIsValid($link);
    }

    public function create(User $user)
    {
        return $user->hasPermission('links.create');
    }

    public function update(User $user, ShareableLink $link)
    {
        return $user->hasPermission('links.update') ||
            $link->user_id === $user->id;
    }

    public function destroy(User $user, ShareableLink $link)
    {
        return $user->hasPermission('links.delete') ||
            $link->user_id === $user->id;
    }
}
