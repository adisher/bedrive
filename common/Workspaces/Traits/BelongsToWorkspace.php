<?php

namespace Common\Workspaces\Traits;

use Common\Workspaces\ActiveWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

trait BelongsToWorkspace
{
    protected string $ownerColumn = 'user_id';

    protected static function booted(): void
    {
        static::creating(function (Model $builder) {
            $builder->workspace_id = app(ActiveWorkspace::class)->id;
        });
    }

    public function scopeForActiveWorkspaceOrOwner(Builder $builder, int|string $userId): Builder
    {
        if ($workspaceId = app(ActiveWorkspace::class)->id) {
            $builder->where('workspace_id', $workspaceId);
        }  else {
            $builder->where($this->ownerColumn, $userId);
        }
        return $builder;
    }

    protected function workspaceId(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ?? 0,
            set: fn($value) => $value ?? 0,
        );
    }
}
