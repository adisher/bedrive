<?php

namespace Common\Tags;

use Carbon\Carbon;
use Common\Files\FileEntry;
use Common\Search\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Tag extends Model
{
    use Searchable;

    const MODEL_TYPE = 'tag';
    const DEFAULT_TYPE = 'default';

    protected $hidden = ['pivot'];
    protected $guarded = ['id'];
    protected $casts = ['id' => 'integer'];
    protected $appends = ['model_type'];

    public function files(): MorphToMany
    {
        return $this->morphedByMany(FileEntry::class, 'taggable');
    }

    public function attachEntries(array $ids, int $userId = null)
    {
        if ($userId) {
            $ids = collect($ids)->mapWithKeys(function ($id) use ($userId) {
                return [$id => ['user_id' => $userId]];
            });
        }

        $this->files()->syncWithoutDetaching($ids);
    }

    public function detachEntries(array $ids, int $userId = null)
    {
        $query = $this->files();

        if ($userId) {
            $query->wherePivot('user_id', $userId);
        }

        $query->detach($ids);
    }

    public function insertOrRetrieve(
        Collection|array $tags,
        ?string $type = 'custom',
        ?int $userId = null,
    ): Collection {
        if (!$tags instanceof Collection) {
            $tags = collect($tags);
        }

        $tags = $tags->filter()->map(function ($tag) use ($userId) {
            if (is_string($tag)) {
                return [
                    'name' => $tag,
                    'display_name' => $tag,
                ];
            } else {
                return [
                    'name' => $tag['name'],
                    'display_name' => Arr::get(
                        $tag,
                        'display_name',
                        $tag['name'],
                    ),
                ];
            }
        });

        $tags->transform(function (array $tag) {
            $tag['name'] = slugify($tag['name']);
            return $tag;
        });

        $existing = $this->getByNames($tags->pluck('name'), $type, $userId);

        $new = $tags->filter(function ($tag) use ($existing) {
            return !$existing->first(function ($existingTag) use ($tag) {
                return slugify($existingTag['name']) === slugify($tag['name']);
            });
        });

        if ($new->isNotEmpty()) {
            $new->transform(function ($tag) use ($type, $userId) {
                $tag['created_at'] = Carbon::now();
                $tag['updated_at'] = Carbon::now();
                if ($type) {
                    $tag['type'] = $type;
                }
                if ($userId) {
                    $tag['user_id'] = $userId;
                }
                return $tag;
            });
            $this->insert($new->toArray());
            return $this->getByNames($tags->pluck('name'), $type, $userId);
        } else {
            return $existing;
        }
    }

    public function getByNames(
        Collection $names,
        string $type = null,
        int $userId = null,
    ): Collection {
        $query = $this->whereIn('name', $names);
        if ($type) {
            $query->where('type', $type);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->get();
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' =>
                $this->name &&
                $this->display_name &&
                $this->name !== $this->display_name
                    ? $this->display_name
                    : null,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'type' => $this->type,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return ['id', 'type', 'created_at', 'updated_at'];
    }

    public static function getModelTypeAttribute(): string
    {
        return static::MODEL_TYPE;
    }
}
