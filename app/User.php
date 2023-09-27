<?php

namespace App;

use Common\Auth\BaseUser;
use Common\Workspaces\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens, HasFactory;

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Specifies the user's FCM token
     */
    public function routeNotificationForFcm(): string|array|null
    {
        return $this->fcmTokens()->first()->token ?? null;
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    public function loadFcmToken(): ?string
    {
        if ($this->currentAccessToken()) {
            $token =
                $this->fcmTokens()
                    ->where('device_id', $this->currentAccessToken()->name)
                    ->first()->token ?? null;
            $this['fcm_token'] = $token;
            return $token;
        }
        return null;
    }
}
