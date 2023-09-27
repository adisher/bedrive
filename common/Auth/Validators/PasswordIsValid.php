<?php

namespace Common\Auth\Validators;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\Hash;

class PasswordIsValid implements InvokableRule
{
    public bool $implicit = true;

    public function __construct(protected string $password)
    {
    }

    public function __invoke($attribute, $value, $fail)
    {
        if (!Hash::check($value, $this->password)) {
            $fail('Password does not match.')->translate();
        }
    }
}
