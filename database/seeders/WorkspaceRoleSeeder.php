<?php

namespace Database\Seeders;

use Common\Auth\Permissions\Permission;
use Common\Auth\Roles\Role;
use Common\Core\Values\ValueLists;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WorkspaceRoleSeeder extends Seeder
{
    public function __construct(private Role $role)
    {
    }

    public function run()
    {
        if ($this->role->where('type', 'workspace')->count() === 0) {
            $permissions = $this->loadPermissions();
            $role = $this->role->create([
                'name' => 'Workspace Admin',
                'description' =>
                    'Manage workspace content, members, settings and invite new members.',
                'type' => 'workspace',
            ]);
            $role->permissions()->sync($permissions);

            $editorPermissions = $permissions->filter(
                fn(Permission $permission) => $permission->group !==
                    'workspace_members',
            );
            $role = $this->role->create([
                'name' => 'Workspace Editor',
                'description' => 'Add, edit, move and delete workspace files.',
                'type' => 'workspace',
            ]);
            $role->permissions()->sync($editorPermissions);

            $memberPermissions = $permissions->filter(
                fn(Permission $permission) => $permission->group !==
                    'workspace_members' &&
                    Str::endsWith($permission->name, 'view'),
            );
            $role = $this->role->create([
                'name' => 'Workspace Contributor',
                'description' => 'Add and edit files.',
                'type' => 'workspace',
            ]);
            $role->permissions()->sync($memberPermissions);
        }
    }

    private function loadPermissions(): Collection
    {
        return app(ValueLists::class)->workspacePermissions();
    }
}
