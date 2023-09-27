<?php

namespace Common\Notifications;

use App\User;
use File;
use Ramsey\Uuid\Uuid;

class SubscribeUserToNotifications
{
    public function execute(User $user, ?array $notificationIds)
    {
        $config = File::getRequire(
            resource_path('defaults/notification-settings.php'),
        );
        if (is_null($notificationIds)) {
            $notificationIds = collect($config['subscriptions'])
                ->map(function ($group) {
                    return collect($group['subscriptions'])->pluck('notif_id');
                })
                ->flatten(1)
                ->toArray();
        }

        $rows = array_map(function ($notifId) use ($config, $user) {
            return [
                'id' => Uuid::uuid4(),
                'notif_id' => $notifId,
                'channels' => json_encode(
                    collect($config['available_channels'])->mapWithKeys(
                        fn($channel) => [$channel => true],
                    ),
                ),
                'user_id' => $user->id,
            ];
        }, $notificationIds);

        $user->notificationSubscriptions()->delete();
        $user->notificationSubscriptions()->insert($rows);
    }
}
