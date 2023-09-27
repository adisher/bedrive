<?php

namespace App\Actions\Fortify;

use App\User;
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
            ],
            'password' => $this->passwordRules(),
            'token_name' => 'string|min:3|max:50',
        ])->validate();

        return app(UserRepository::class)->create($input);
    }
}
