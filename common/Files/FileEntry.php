<?php namespace Common\Files;

use App\User;
use Arr;
use Auth;
use Common\Files\Traits\HandlesEntryPaths;
use Common\Files\Traits\HashesId;
use Common\Search\Searchable;
use Common\Tags\HandlesTags;
use Common\Tags\Tag;
use Common\Workspaces\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class FileEntry extends Model
{
    use SoftDeletes,
        HashesId,
        HandlesEntryPaths,
        HandlesTags,
        Searchable,
        BelongsToWorkspace;

    public const MODEL_TYPE = 'fileEntry';
    protected $guarded = ['id'];
    protected $hidden = ['pivot', 'preview_token'];
    protected $appends = ['hash', 'url'];
    protected $casts = [
        'id' => 'integer',
        'file_size' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'thumbnail' => 'boolean',
        'public' => 'boolean',
        'workspace_id' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            FileEntryUser::class,
            'model',
            'file_entry_models',
            'file_entry_id',
            'model_id',
        )
            ->using(FileEntryPivot::class)
            ->select(
                'first_name',
                'last_name',
                'email',
                'users.id',
                'avatar',
                'model_type',
            )
            ->withPivot('owner', 'permissions')
            ->withTimestamps()
            ->orderBy('file_entry_models.created_at');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')->withoutGlobalScope(
            'fsType',
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->wherePivot(
            'user_id',
            Auth::id() ?? null,
        );
    }

    public function getUrlAttribute(string $value = null): ?string
    {
        if ($value) {
            return $value;
        }
        if (
            !isset($this->attributes['type']) ||
            $this->attributes['type'] === 'folder'
        ) {
            return null;
        }

        if (Arr::get($this->attributes, 'public')) {
            return Storage::disk('public')->url(
                "$this->disk_prefix/$this->file_name",
            );
        } elseif ($endpoint = config('common.site.file_preview_endpoint')) {
            return "$endpoint/uploads/{$this->file_name}/{$this->file_name}";
        } else {
            return "api/v1/file-entries/{$this->attributes['id']}";
        }
    }

    public function getStoragePath(bool $useThumbnail = false): string
    {
        $fileName = $useThumbnail ? 'thumbnail.jpg' : $this->file_name;
        if ($this->public) {
            return "$this->disk_prefix/$fileName";
        } else {
            return "$this->file_name/$fileName";
        }
    }

    public function getDisk()
    {
        if ($this->public) {
            return Storage::drive('public');
        } else {
            return Storage::drive('uploads');
        }
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeWhereRootOrParentNotTrashed(Builder $query)
    {
        return $query
            ->whereNull('parent_id')
            ->orWhereHas('parent', function (Builder $query) {
                return $query->whereNull('deleted_at');
            });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Select all entries user has access to.
     */
    public function scopeWhereUser(
        Builder $builder,
        int $userId,
        bool|null $owner = null,
    ): Builder {
        return $builder->whereIn($this->qualifyColumn('id'), function (
            $query,
        ) use ($userId, $owner) {
            $query
                ->select('file_entry_id')
                ->from('file_entry_models')
                ->where('model_id', $userId)
                ->where('model_type', User::class);

            // if $owner is not null, need to load either only
            // entries user owns or entries user does not own
            //if $owner is null, load all entries
            if (!is_null($owner)) {
                $query->where('owner', $owner);
            }
        });
    }

    /**
     * Select only entries that were not created by specified user.
     */
    public function scopeWhereNotOwner(Builder $builder, int $userId): Builder
    {
        return $this->scopeWhereUser($builder, $userId, false);
    }

    /**
     * Get path of specified entry.
     *
     * @param int $id
     * @return string
     */
    public function findPath($id)
    {
        $entry = $this->find($id, ['path']);
        return $entry ? $entry->getRawOriginal('path') : '';
    }

    /**
     * Return file entry name with extension.
     * @return string
     */
    public function getNameWithExtension()
    {
        if (!$this->exists) {
            return '';
        }

        $extension = pathinfo($this->name, PATHINFO_EXTENSION);

        if (!$extension && $this->extension) {
            return $this->name . '.' . $this->extension;
        }

        return $this->name;
    }

    public function getTotalSize(): int
    {
        if ($this->type === 'folder') {
            return $this->allChildren()->sum('file_size');
        } else {
            return $this->file_size;
        }
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        // ID might be with extension: "4546.mp4" or as hash: "ja4d5ad4" or int: 4546
        $intValue = (int) $value;
        if ($intValue === 0) {
            $intValue = $this->decodeHash($intValue);
        }
        return $this->withTrashed()->findOrFail($intValue);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_size' => $this->file_size,
            'mime' => $this->mime,
            'extension' => $this->extension,
            'owner_id' => $this->owner_id,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
            'deleted_at' => $this->deleted_at->timestamp ?? '_null',
            'public' => $this->public,
            'description' => $this->description,
            'password' => $this->password,
            'type' => $this->type,
            'workspace_id' => $this->workspace_id ?? '_null',
            'tags' => $this->tags->pluck('name'),
        ];
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->type,
            'image' => null,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'owner_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'file_size',
            'public',
            'password',
            'type',
            'workspace_id',
        ];
    }
}
