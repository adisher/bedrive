<?php

namespace App\Services\Links;

use App\ShareableLink;
use Auth;
use Hash;

trait ValidatesLinkPassword
{
    private function passwordIsValid(ShareableLink $link): bool
    {
        // link has no password
        if (!$link->password) {
            return true;
        }
        if ($link->user_id === Auth::id()) {
          return true;
        }
        return Hash::check(request()->get('password'), $link->password);
    }
}
