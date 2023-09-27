<?php namespace Common\Auth;

use App\User;
use Carbon\Carbon;
use Common\Auth\Events\UserCreated;
use Common\Auth\Events\UsersDeleted;
use Common\Auth\Permissions\Traits\SyncsPermissions;
use Common\Auth\Roles\Role;
use Common\Billing\Subscription;
use Common\Domains\Actions\DeleteCustomDomains;
use Common\Domains\CustomDomain;
use Common\Files\Actions\Deletion\PermanentlyDeleteEntries;
use Common\Pages\CustomPage;
use Common\Settings\Settings;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    use SyncsPermissions;

    public function __construct(
        protected User $user,
        protected Role $role,
        protected Settings $settings
    ) {
    }

    public function create(array $params): User
    {
        $user = $this->user->forceCreate($this->formatParams($params));

        try {
            if (
                !isset($params['roles']) ||
                !$this->attachRoles($user, $params['roles'])
            ) {
                $this->assignDefaultRole($user);
            }

            if ($permissions = Arr::get($params, 'permissions')) {
                $this->syncPermissions($user, $permissions);
            }
        } catch (Exception $e) {
            //delete user if there were any errors creating/assigning
            //purchase codes or roles, so there are no artifacts left
            $user->delete();
            throw $e;
        }

        event(new UserCreated($user));

        return $user;
    }

    public function update(User $user, array $params): User
    {
        $user->forceFill($this->formatParams($params, 'update'))->save();

        // make sure roles and permission are not removed
        // if they are not specified at all in params
        if (array_key_exists('roles', $params)) {
            $this->attachRoles($user, Arr::get($params, 'roles'));
        }
        if (array_key_exists('permissions', $params)) {
            $this->syncPermissions($user, Arr::get($params, 'permissions'));
        }

        return $user->load(['roles', 'permissions']);
    }

    public function deleteMultiple(array|Collection $ids): int
    {
        $users = $this->user->whereIn('id', $ids)->get();

        $users->each(function (User $user) {
            $user->social_profiles()->delete();
            $user->roles()->detach();
            $user->notifications()->delete();
            $user->permissions()->detach();

            if ($user->subscribed()) {
                $user->subscriptions->each(function (
                    Subscription $subscription
                ) {
                    $subscription->cancelAndDelete();
                });
            }

            $user->delete();

            $entryIds = $user
                ->entries(['owner' => true])
                ->pluck('file_entries.id');
            app(PermanentlyDeleteEntries::class)->execute($entryIds);
        });

        // delete domains
        $domainIds = app(CustomDomain::class)
            ->whereIn('user_id', $ids)
            ->pluck('id');
        app(DeleteCustomDomains::class)->execute($domainIds->toArray());

        // delete custom pages
        CustomPage::whereIn('user_id', $ids)->delete();

        event(new UsersDeleted($users));

        return $users->count();
    }

    protected function formatParams(
        array $params,
        string $type = 'create'
    ): array {
        $formatted = [
            'first_name' => $params['first_name'] ?? null,
            'last_name' => $params['last_name'] ?? null,
            'language' => $params['language'] ?? config('app.locale'),
            'country' => $params['country'] ?? null,
            'timezone' => $params['timezone'] ?? null,
        ];

        if ($type === 'update') {
            $formatted = array_filter(
                $formatted,
                fn($value) => !is_null($value),
            );
        }

        if (isset($params['email_verified_at'])) {
            if ($params['email_verified_at'] === true) {
                $formatted['email_verified_at'] = Carbon::now();
            } elseif ($params['email_verified_at'] === false) {
                $formatted['email_verified_at'] = null;
            } else {
                $formatted['email_verified_at'] = $params['email_verified_at'];
            }
        }

        // for registration and social login
        if (
            !app(Settings::class)->get('require_email_confirmation') &&
            !Arr::get($formatted, 'email_verified_at')
        ) {
            $formatted['email_verified_at'] = Carbon::now();
        }

        if (isset($params['avatar'])) {
            $formatted['avatar'] = $params['avatar'];
        }

        if (array_key_exists('available_space', $params)) {
            $formatted['available_space'] = is_null($params['available_space'])
                ? null
                : (int) $params['available_space'];
        }

        if ($type === 'create') {
            $formatted['email'] = $params['email'];
            $formatted['password'] = Arr::get($params, 'password')
                ? Hash::make($params['password'])
                : null;
        } elseif ($type === 'update' && Arr::get($params, 'password')) {
            $formatted['password'] = Hash::make($params['password']);
        }

        return $formatted;
    }

    /**
     * Assign roles to user, if any are given.
     */
    public function attachRoles(
        User $user,
        array $roles,
        string $type = 'sync'
    ): bool {
        if (empty($roles) && $type === 'attach') {
            return false;
        }
        $roleIds = $this->role
            ->whereIn('id', $roles)
            ->get()
            ->pluck('id');
        $user->roles()->$type($roleIds);
        return $roleIds->count();
    }

    /**
     * Add specified permissions to user.
     *
     * @param User $user
     * @param array $permissions
     * @return User
     */
    public function addPermissions(User $user, $permissions)
    {
        $existing = $user->loadPermissions()->permissions;

        foreach ($permissions as $permission) {
            $existing[$permission] = 1;
        }

        $user->forceFill(['permissions' => $existing])->save();

        return $user;
    }

    /**
     * Remove specified permissions from user.
     *
     * @param User $user
     * @param array $permissions
     * @return User
     */
    public function removePermissions(User $user, $permissions)
    {
        $existing = $user->loadPermissions()->permissions;

        foreach ($permissions as $permission) {
            unset($existing[$permission]);
        }

        $user->forceFill(['permissions' => $existing])->save();

        return $user;
    }

    /**
     * Assign default role to given user.
     *
     * @param User $user
     */
    protected function assignDefaultRole(User $user)
    {
        $defaultRole = $this->role->getDefaultRole();

        if ($defaultRole) {
            $user->roles()->attach($defaultRole->id);
        }
    }
}
