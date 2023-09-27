<?php

namespace Common\Auth\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

class OptionalAuthenticate extends Authenticate
{
    // prevent authentication exception if user is not logged in at all. This will be handled in policies instead
    protected function unauthenticated($request, array $guards)
    {
        //
    }
}
