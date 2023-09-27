<?php

namespace Common\Channels;

use App\Channel;
use App\User;
use Common\Core\Policies\BasePolicy;

class ChannelPolicy extends BasePolicy
{
    public function index(?User $user, $userId = null)
    {
        return $user->hasPermission('channels.view');
    }

    public function show(?User $user, Channel $channel)
    {
        return $user->hasPermission('channels.view') ||
            $user->hasPermission('music.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('channels.create');
    }

    public function update(User $user, Channel $channel)
    {
        return $user->hasPermission('channels.update');
    }

    public function destroy(User $user, $channelIds)
    {
        return $user->hasPermission('channels.delete');
    }
}
