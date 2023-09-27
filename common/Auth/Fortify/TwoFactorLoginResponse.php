<?php

namespace Common\Auth\Fortify;

use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class TwoFactorLoginResponse extends LoginResponse implements
    TwoFactorLoginResponseContract
{
}
