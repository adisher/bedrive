<?php

namespace Common\Auth\Permissions;

use Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Permission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'advanced' => 'integer',
    ];

    protected $hidden = ['pivot', 'permissionable_type'];

    /**
     * @param string|array $value
     * @return Collection
     */
    public function getRestrictionsAttribute($value)
    {
        // if loading permissions via parent (user, role, plan) return restrictions
        // stored on pivot table, otherwise return restrictions stored on permission itself
        $value = $this->pivot ? $this->pivot->restrictions : $value;
        if ( ! $value) $value = [];
        return collect(is_string($value) ? json_decode($value, true) : $value)->values();
    }

    public function setRestrictionsAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['restrictions'] = json_encode(array_values($value));
        }
    }

    public function getRestrictionValue(string $name): int | bool | null
    {
        $restriction = $this->restrictions->first(function($restriction) use($name) {
            return $restriction['name'] === $name;
        });

        return Arr::get($restriction, 'value') ?? null;
    }

    /**
     * Merge restrictions from specified permission into this permission.
     */
    public function mergeRestrictions(Permission $permission = null): self
    {
        if ($permission) {
            $permission->restrictions->each(function($restriction) {
                $exists = $this->restrictions->first(function($r) use($restriction) {
                    return $r['name'] === $restriction['name'];
                });
                if ( ! $exists) {
                    $this->restrictions->push($restriction);
                }
            });
        }
        return $this;
    }
}
