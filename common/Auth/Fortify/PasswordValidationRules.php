<?php

namespace Common\Auth\Fortify;

use Laravel\Fortify\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     */
    protected function passwordRules(): array
    {
        $password = new Password();
        $password->length(5);
        return ['required', 'string', $password, 'confirmed'];
    }
}
