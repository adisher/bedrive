<?php

namespace Common\Auth\Permissions\Traits;

use Common\Auth\Permissions\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasPermissionsRelation
{
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(Permission::class, 'permissionable')
            ->withPivot('restrictions')
            ->select('name', 'permissions.id', 'permissions.restrictions');
    }

    public function hasPermission(string $name): bool
    {
        return !is_null($this->getPermission($name)) ||
            !is_null($this->getPermission('admin'));
    }

    public function hasExactPermission(string $name): bool
    {
        return !is_null($this->getPermission($name));
    }

    public function getPermission(string $name): Permission|null
    {
        if (method_exists($this, 'loadPermissions')) {
            $this->loadPermissions();
        }

        foreach ($this->permissions as $permission) {
            if ($permission->name === $name) {
                return $permission;
            }
        }

        return null;
    }

    public function getRestrictionValue(
        string $permissionName,
        string $restriction,
    ): int|bool|null {
        $permission = $this->getPermission($permissionName);
        return $permission?->getRestrictionValue($restriction);
    }
}
