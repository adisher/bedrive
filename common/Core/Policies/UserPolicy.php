<?php namespace Common\Core\Policies;

use App\User;

class UserPolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->hasPermission($user, 'users.view');
    }

    public function show(?User $current, User $requested)
    {
        return $this->hasPermission($current, 'users.view') ||
            $current->id === $requested->id;
    }

    public function store(User $user)
    {
        return $this->hasPermission($user, 'users.create');
    }

    public function update(User $current, User $toUpdate = null)
    {
        // user has proper permissions
        if ($this->hasPermission($current, 'users.update')) {
            return true;
        }

        // no permissions and not trying to update his own model
        if (!$toUpdate || $current->id !== $toUpdate->id) {
            return false;
        }

        // user should not be able to change his own permissions or roles
        if (
            $this->request->get('permissions') ||
            $this->request->get('roles')
        ) {
            return false;
        }

        return true;
    }

    public function destroy(User $user, array $userIds)
    {
        $deletingOwnAccount = collect($userIds)->every(function (
            int $userId
        ) use ($user) {
            return $userId === $user->id;
        });

        return $deletingOwnAccount || $this->hasPermission($user, 'users.delete');
    }
}
