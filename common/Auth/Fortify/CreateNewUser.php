<?php

namespace Common\Auth\Fortify;

use App\User;
use Closure;
use Common\Auth\UserRepository;
use Common\Settings\Settings;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        if (app(Settings::class)->get('registration.disable')) {
            abort(404);
        }

        Validator::make($input, [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
                function (string $attribute, mixed $value, Closure $fail) {
                    if (!self::emailIsValid($value)) {
                        $fail(__('This domain is blacklisted.'));
                    }
                },
            ],
            'password' => $this->passwordRules(),
            'token_name' => 'string|min:3|max:50',
        ])->validate();

        return app(UserRepository::class)->create($input);
    }

    public static function emailIsValid(string $email): bool
    {
        $blacklistedDomains = explode(
            ',',
            app(Settings::class)->get('auth.domain_blacklist', ''),
        );
        if ($blacklistedDomains) {
            $domain = explode('@', $email)[1] ?? null;
            if ($domain && in_array($domain, $blacklistedDomains)) {
                return false;
            }
        }

        return true;
    }
}
