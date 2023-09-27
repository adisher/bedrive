<?php

namespace Common\Channels;

use App\User;
use Carbon\Carbon;
use Common\Search\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class BaseChannel extends Model
{
    use Searchable;

    const MODEL_TYPE = 'channel';
    protected $guarded = ['id'];
    protected $appends = ['model_type'];
    protected $hidden = ['pivot'];

    protected $casts = [
        'id' => 'integer',
    ];
    protected static function booted()
    {
        // touch parent channels
        static::updated(function (self $channel) {
            $parentIds = DB::table('channelables')
                ->where('channelable_type', static::class)
                ->where('channelable_id', $channel->id)
                ->pluck('channel_id');
            static::withoutEvents(function () use ($parentIds) {
                static::whereIn('id', $parentIds)->update([
                    'updated_at' => Carbon::now(),
                ]);
            });
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'channelable')->withPivot([
            'id',
            'channelable_id',
            'order',
        ]);
    }

    public function channels(): MorphToMany
    {
        return $this->morphedByMany(static::class, 'channelable')->withPivot([
            'id',
            'channelable_id',
            'order',
        ]);
    }

    public function getConfigAttribute(): ?array
    {
        return isset($this->attributes['config'])
            ? json_decode($this->attributes['config'], true)
            : [];
    }

    public function setConfigAttribute($value)
    {
        $this->attributes['config'] = is_array($value)
            ? json_encode($value)
            : $value;
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'description' =>
                $this->description ||
                Arr::get($this->attributes, 'config.seoDescription'),
            'model_type' => static::MODEL_TYPE,
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }

    public static function filterableFields(): array
    {
        return ['id'];
    }

    public static function getModelTypeAttribute(): string
    {
        return static::MODEL_TYPE;
    }

    public function loadRestrictionModel(string $urlParam = null)
    {
        $restriction = null;
        $modelName = $this->config['restriction'];
        $modelId = $this->config['restrictionModelId'];
        $model = app(modelTypeToNamespace($modelName))
            ->select(['id', 'name', 'display_name']);

        if ($modelId === 'urlParam' && $urlParam) {
            $restriction = $model->where('name', $urlParam)->first();
        } else if (isset($modelId) && $modelId !== 'urlParam') {
            $restriction = $model->find('id', $modelId);
        }

        if ($restriction && !$restriction->display_name) {
            $restriction->display_name = ucwords($restriction->name);
        }

        return $restriction;
    }

    public function loadContent(array $params = [], self $parent = null): static
    {
        $channelContent = app(LoadChannelContent::class)->execute(
            $this,
            $params,
            $parent,
        );

        if (Arr::get($params, 'normalizeContent') && $channelContent) {
            $channelContent->transform(fn($item) => $item->toNormalizedArray());
        }

        $this->setRelation('content', $channelContent);
        return $this;
    }

    public function updateContentFromExternal(
        string $autoUpdateMethod = null,
    ): void {
        $method =
            $autoUpdateMethod ?? Arr::get($this->config, 'autoUpdateMethod');
        $content = $this->loadContentFromExternal($method);

        // bail if we could not fetch any content
        if (!$content || $content->isEmpty()) {
            return;
        }

        // detach all channel items from the channel
        DB::table('channelables')
            ->where(['channel_id' => $this->id])
            ->delete();

        // group content by model type (track, album, playlist etc)
        // and attach each group via its own separate relation
        $groupedContent = $content->groupBy('model_type');
        $groupedContent->each(function (Collection $contentGroup, $modelType) {
            $pivots = $contentGroup->mapWithKeys(
                fn($item, $index) => [$item['id'] => ['order' => $index]],
            );
            // track => tracks
            $relation = Str::plural($modelType);
            $this->$relation()->syncWithoutDetaching($pivots->toArray());
        });

        // clear channel cache, it's based on updated_at timestamp
        $this->touch();
    }

    abstract protected function loadContentFromExternal(
        string $autoUpdateMethod,
    ): Collection|array|null;
}
