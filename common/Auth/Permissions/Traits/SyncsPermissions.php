<?php

namespace Common\Auth\Permissions\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait SyncsPermissions
{
    public function syncPermissions(
        Model $model,
        array|Collection $permissions
    ): void {
        $permissionIds = collect($permissions)->mapWithKeys(function (
            $permission
        ) {
            $restrictions = Arr::get($permission, 'restrictions', []);
            return [
                $permission['id'] => [
                    'restrictions' => collect($restrictions)
                        ->filter(function ($restriction) {
                            return isset($restriction['value']);
                        })
                        ->map(function ($restriction) {
                            return [
                                'name' => $restriction['name'],
                                'value' => $restriction['value'],
                            ];
                        }),
                ],
            ];
        });
        $model->permissions()->sync($permissionIds);
    }
}
