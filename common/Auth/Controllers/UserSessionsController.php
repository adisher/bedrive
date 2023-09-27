<?php

namespace Common\Auth\Controllers;

use Common\Auth\ActiveSession;
use Common\Core\BaseController;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class UserSessionsController extends BaseController
{
    public function index()
    {
        $sessions = ActiveSession::where('user_id', Auth::user()->id)
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get()
            ->map(function (ActiveSession $session) {
                $agent = new Agent(null, $session->user_agent);
                $location = geoip($session->ip_address);

                $isCurrentDevice = $session->session_id
                    ? $session->session_id ===
                        request()
                            ->session()
                            ->getId()
                    : $session->token ===
                        Auth::user()->currentAccessToken()->token;

                return [
                    'id' => $session->id,
                    'platform' => $agent->platform(),
                    'device_type' => $agent->deviceType(),
                    'browser' => $agent->browser(),
                    'country' => $location->country,
                    'city' => $location->city,
                    'ip_address' => config('common.site.demo')
                        ? 'Hidden on demo site'
                        : $session->ip_address,
                    'is_current_device' => $isCurrentDevice,
                    'last_active' => $session->updated_at,
                ];
            })
            ->values();

        return $this->success(['sessions' => $sessions]);
    }

    public function LogoutOtherSessions(StatefulGuard $guard)
    {
        $data = $this->validate(request(), [
            'password' => 'required',
        ]);

        $guard->logoutOtherDevices($data['password']);

        ActiveSession::where('user_id', $guard->id())
            ->whereNotNull('session_id')
            ->where(
                'session_id',
                '!=',
                request()
                    ->session()
                    ->getId(),
            )
            ->delete();

        return $this->success();
    }
}
