<?php

namespace Common\Votes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    public $timestamps = false;

    public function scopeWithCurrentUserVotes(Builder $query): Builder
    {
        return $query
            ->where('user_id', auth()->id())
            ->orWhere('user_ip', getIp());
    }
}
