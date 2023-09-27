<?php

namespace Common\Auth\Fortify;

use App\User;
use Hash;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->instance(LoginResponseContract::class, new LoginResponse());
        $this->app->instance(
            TwoFactorLoginResponseContract::class,
            new TwoFactorLoginResponse(),
        );
        $this->app->instance(
            LogoutResponseContract::class,
            new LogoutResponse(),
        );
        $this->app->instance(
            RegisterResponseContract::class,
            new RegisterResponse(),
        );
    }

    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->session()->get('login.id'),
            );
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (!CreateNewUser::emailIsValid($request->email)) {
                $this->throwFailedAuthenticationException(
                    $request,
                    __('This domain is blacklisted.'),
                );
            }

            if ($user?->isBanned()) {
                $comment = $user->bans()->first()->comment;
                $this->throwFailedAuthenticationException(
                    $request,
                    $comment
                        ? __('Banned: :reason', ['reason' => $comment])
                        : __('This user is banned.'),
                );
            }

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
        });
    }

    protected function throwFailedAuthenticationException(
        Request $request,
        string $message,
    ) {
        app(LoginRateLimiter::class)->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [$message],
        ]);
    }
}
