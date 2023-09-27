<?php

namespace Common\Auth\Controllers;

use App\User;
use Common\Core\BaseController;
use Common\Core\Bootstrap\MobileBootstrapData;
use Common\Settings\Settings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class MobileAuthController extends BaseController
{
    public function login(Request $request)
    {
        $this->validate($request, [
            Fortify::username() => 'required|string|email_verified',
            'password' => 'required|string',
            'token_name' => 'required|string|min:3|max:100',
        ]);

        $user = User::where(
            Fortify::username(),
            $request->get(Fortify::username()),
        )->first();

        if (
            !$user ||
            !Hash::check($request->get('password'), $user->password)
        ) {
            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        if (app(Settings::class)->get('single_device_login')) {
            Auth::logoutOtherDevices($request->get('password'));
        }

        Auth::login($user);

        $bootstrapData = app(MobileBootstrapData::class)
            ->init()
            ->refreshToken($request->get('token_name'))
            ->get();

        return $this->success($bootstrapData);
    }

    public function register(
        Request $request,
        CreatesNewUsers $creator
    ): RegisterResponse {
        event(new Registered(($user = $creator->create($request->all()))));

        Auth::login($user);

        return app(RegisterResponse::class);
    }
}
