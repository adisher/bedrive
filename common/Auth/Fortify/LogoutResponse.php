<?php

namespace Common\Auth\Fortify;

use Common\Core\Bootstrap\BootstrapData;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): JsonResponse
    {
        $data = app(BootstrapData::class)
            ->init()
            ->getEncoded();

        return response()->json([
            'bootstrapData' => $data,
            'status' => 'success',
        ]);
    }
}
