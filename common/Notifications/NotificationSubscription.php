<?php

namespace Common\Notifications;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class NotificationSubscription extends Model
{
    protected $guarded = ['id'];
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'user_id' => 'integer',
        'channels' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }
}
