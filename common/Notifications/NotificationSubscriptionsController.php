<?php

namespace Common\Notifications;

use App\User;
use Common\Core\BaseController;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;

class NotificationSubscriptionsController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(User $user): JsonResponse
    {
        $response = $this->getConfig();
        $subs = $user->notificationSubscriptions;
        $response['user_selections'] = $subs;

        return $this->success($response);
    }

    public function update(User $user): JsonResponse
    {
        $this->validate(request(), [
            'selections' => 'present|array',
            'selections.*.notif_id' => 'required|string',
            'selections.*.channels' => 'required|array',
        ]);

        foreach (request()->get('selections') as $selection) {
            $subscription = $user
                ->notificationSubscriptions()
                ->firstOrNew(['notif_id' => $selection['notif_id']]);
            $newChannels = $subscription['channels'];
            // can update state of all channels at once or only a single channel
            foreach ($selection['channels'] as $newChannel => $isSubscribed) {
                $newChannels[$newChannel] = $isSubscribed;
            }
            $subscription->fill(['channels' => $newChannels])->save();
        }

        return $this->success();
    }

    private function getConfig()
    {
        return app(Filesystem::class)->getRequire(
            resource_path('defaults/notification-settings.php'),
        );
    }
}
