<?php

namespace App;

use Common\Workspaces\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Folder extends FileEntry
{
    use HasFactory, BelongsToWorkspace;

    protected $attributes = [
        'type' => 'folder',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('fsType', function (Builder $builder) {
            $builder->where('type', 'folder');
        });
    }
}
