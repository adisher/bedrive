<?php namespace Common\Pages;

use App\User;
use Common\Search\Searchable;
use Common\Tags\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class CustomPage extends Model
{
    use Searchable, HasFactory;

    const PAGE_TYPE = 'default';
    const MODEL_TYPE = 'customPage';

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'hide_nav' => 'boolean',
        'meta' => 'json',
    ];

    protected $appends = ['model_type'];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = slugify($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'slug' => $this->slug,
            'type' => $this->type,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
            'user_id' => $this->user_id,
            'workspace_id' => $this->workspace_id ?? '_null',
        ];
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'image' => $this->meta['image'] ?? null,
            'description' => Str::limit($this->body, 100),
            'model_type' => static::MODEL_TYPE,
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'user_id',
            'created_at',
            'updated_at',
            'type',
            'workspace_id',
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return static::MODEL_TYPE;
    }

    public static function factory(): CustomPageFactory
    {
        return CustomPageFactory::new();
    }
}
