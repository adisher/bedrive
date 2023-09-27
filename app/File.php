<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends FileEntry
{
    use HasFactory;

    protected $table = 'file_entries';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('fsType', function (Builder $builder) {
            $builder->where('type', '!=', 'folder');
        });
    }
}
