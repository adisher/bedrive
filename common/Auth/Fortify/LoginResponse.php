<?php

namespace Common\Auth\Fortify;

use Common\Core\Bootstrap\BootstrapData;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse
    {
        if (
            $request->get('password') &&
            app(Settings::class)->get('single_device_login')
        ) {
            Auth::logoutOtherDevices($request->get('password'));
        }

        $data = app(BootstrapData::class)
            ->init()
            ->getEncoded();

        return response()->json([
            'bootstrapData' => $data,
            'two_factor' => false,
        ]);
    }
}
