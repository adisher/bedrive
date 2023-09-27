<?php

namespace Common\Notifications;

use Auth;
use Carbon\Carbon;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends BaseController
{
    public function __construct(
        protected DatabaseNotification $notification,
        protected Request $request
    ) {
        $this->middleware('auth');
    }

    public function index(): JsonResponse
    {
        $pagination = Auth::user()
            ->notifications()
            ->paginate($this->request->get('perPage', 10));

        return $this->success(['pagination' => $pagination]);
    }

    public function markAsRead()
    {
        $this->validate($this->request, [
            'ids' => 'required|array',
        ]);

        $now = Carbon::now();

        $this->notification
            ->whereIn('id', $this->request->get('ids'))
            ->update(['read_at' => $now]);

        $unreadCount = Auth::user()
            ->unreadNotifications()
            ->count();

        return $this->success(['unreadCount' => $unreadCount, 'date' => $now]);
    }

    public function destroy($ids)
    {
        $ids = explode(',', $ids);
        Auth::user()
            ->notifications()
            ->whereIn('id', $ids)
            ->delete();
    }
}
