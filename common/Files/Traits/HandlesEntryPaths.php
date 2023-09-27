<?php

namespace Common\Files\Traits;

use DB;
use Illuminate\Database\Eloquent\Builder;

trait HandlesEntryPaths
{
    public function getPathAttribute($value): string
    {
        if (!$value) {
            $value = '';
        }

        $parts = explode('/', $value);

        $parts = array_map(function ($part) {
            return $this->decodePathId($part);
        }, array_filter($parts));

        return implode('/', $parts);
    }

    public function setPathAttribute($value)
    {
        if (!$value) {
            $value = '';
        }

        $this->attributes['path'] = $this->encodePath($value);
    }

    public function updatePaths(
        string $oldPath,
        string $newPath,
        $entryIds = null,
    ): void {
        $oldPath = $this->encodePath($oldPath);
        $newPath = $this->encodePath($newPath);

        $query = $this->newQuery();

        if ($entryIds) {
            $query->whereIn('id', $entryIds);
        }

        $query->where('path', 'LIKE', "$oldPath%")->update([
            'path' => DB::raw("REPLACE(path, '$oldPath', '$newPath')"),
        ]);
    }

    /**
     * Loads current model as well as all children.
     */
    public function scopeAllChildren(Builder $builder): Builder
    {
        return $builder->where('path', 'like', $this->attributes['path'] . '%');
    }

    public function getParentIds(): array
    {
        $folderIds = explode('/', $this->path);
        array_pop($folderIds);
        return $folderIds;
    }

    public function scopeAllParents(Builder $builder): Builder
    {
        return $builder->whereIn('id', $this->getParentIds());
    }

    /**
     * Generate full path for current entry, based on its parent.
     */
    public function generatePath(): void
    {
        if (!$this->exists) {
            return;
        }

        $this->path = $this->id;

        if ($this->parent_id && ($parent = $this->find($this->parent_id))) {
            $this->path = "{$parent->path}/$this->path";
        }

        $this->save();
    }

    private function encodePath($path): string
    {
        $parts = explode('/', (string) $path);

        $parts = array_filter($parts);

        $parts = array_map(function ($part) {
            return $this->encodePathId($part);
        }, $parts);

        return implode('/', $parts);
    }

    private function encodePathId($id): string
    {
        return base_convert($id, 10, 36);
    }

    private function decodePathId($id): string
    {
        return base_convert($id, 36, 10);
    }
}
