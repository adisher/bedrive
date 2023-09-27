<?php

namespace Common\Core\Policies;

use App\User;

class TagPolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->hasPermission($user, 'tags.view');
    }

    public function show(?User $user)
    {
        return $this->hasPermission($user, 'tags.view');
    }

    public function store(User $user)
    {
        return $this->hasPermission($user, 'tags.create');
    }

    public function update(User $user)
    {
        return $this->hasPermission($user, 'tags.update');
    }

    public function destroy(User $user)
    {
        return $this->hasPermission($user, 'tags.delete');
    }
}
