<?php

namespace App\Http\Controllers;

use App\ShareableLink;
use Common\Core\BaseController;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareableLinkPasswordController extends BaseController
{
    public function __construct(private ShareableLink $link, private Request $request)
    {
    }

    public function check(string $linkHash): JsonResponse
    {
        $link = $this->link->where('hash', $linkHash)->first();
        $password = $this->request->get('password');

        return $this->success([
            'matches' => $link && Hash::check($password, $link->password),
        ]);
    }
}
