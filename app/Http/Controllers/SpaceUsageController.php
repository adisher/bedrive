<?php

namespace App\Http\Controllers;

use Auth;
use Common\Core\BaseController;
use Common\Files\Actions\GetUserSpaceUsage;
use Illuminate\Http\JsonResponse;

class SpaceUsageController extends BaseController
{
    public function index(): JsonResponse
    {
        $this->authorize('show', Auth::user());

        $usage = app(GetUserSpaceUsage::class)->execute(Auth::user());

        return $this->success($usage);
    }
}
