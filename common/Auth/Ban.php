<?php

namespace Common\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Ban extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Ban $ban) {});
    }

    public function createdBy(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
