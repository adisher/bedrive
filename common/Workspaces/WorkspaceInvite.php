<?php

namespace Common\Workspaces;

use App\User;
use Common\Auth\Traits\HasAvatarAttribute;
use Common\Auth\Traits\HasDisplayNameAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceInvite extends Model
{
    use HasDisplayNameAttribute, HasAvatarAttribute;

    protected $guarded = ['id'];
    protected $appends = ['display_name', 'model_type'];

    protected $keyType = 'orderedUuid';
    public $incrementing = false;

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getModelTypeAttribute(): string
    {
        return 'invite';
    }
}
