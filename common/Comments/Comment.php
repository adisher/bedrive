<?php

namespace Common\Comments;

use App\User;
use Common\Files\Traits\HandlesEntryPaths;
use Common\Search\Searchable;
use Common\Votes\OrdersByWeightedScore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HandlesEntryPaths, HasFactory, Searchable, OrdersByWeightedScore;

    const MODEL_TYPE = 'comment';

    protected $guarded = ['id'];

    protected $hidden = ['commentable_type', 'commentable_id', 'path'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'deleted' => 'boolean',
    ];

    protected $appends = ['depth', 'model_type'];

    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeRootOnly(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id');
    }

    public function scopeChildrenOnly(Builder $builder): Builder
    {
        return $builder->whereNotNull('parent_id');
    }

    public function getDepthAttribute(): int
    {
        if (!$this->path || !$this->parent_id) {
            return 0;
        }
        return count(explode('/', $this->getRawOriginal('path')));
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'deleted' => $this->deleted,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'parent_id',
            'user_id',
            'deleted',
            'commentable_id',
            'commentable_type',
            'created_at',
            'updated_at',
        ];
    }

    protected static function newFactory()
    {
        return CommentFactory::new();
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}
