<?php

namespace Common\Auth\Controllers;

use Common\Core\BaseController;
use Illuminate\Support\Facades\Auth;

class TwoFactorQrCodeController extends BaseController
{
    public function show()
    {
        return $this->success([
            'svg' => Auth::user()->twoFactorQrCodeSvg(),
            'secret' => decrypt(Auth::user()->two_factor_secret),
        ]);
    }
}
