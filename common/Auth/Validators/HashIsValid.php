<?php

namespace Common\Auth\Validators;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\Hash;

class HashIsValid implements InvokableRule
{
    public function __construct(protected string $hashedValue)
    {
    }

    public function __invoke($attribute, mixed $value, $fail)
    {
        if (!Hash::check($value, $this->hashedValue)) {
            return $fail('The :attribute is not valid')->translate();
        }
    }
}
