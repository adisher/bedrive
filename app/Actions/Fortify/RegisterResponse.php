<?php

namespace App\Actions\Fortify;

use Common\Core\Bootstrap\BootstrapData;
use Common\Core\Bootstrap\MobileBootstrapData;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): JsonResponse
    {
        $response = [
            'status' => $request->user()->hasVerifiedEmail()
                ? 'success'
                : 'needs_email_verification',
        ];

        // for mobile
        if ($request->has('token_name')) {
            $bootstrapData = app(MobileBootstrapData::class)->init();
            $bootstrapData->refreshToken($request->get('token_name'));
            $response['boostrapData'] = $bootstrapData->get();

            // for web
        } else {
            $bootstrapData = app(BootstrapData::class)->init();
            $response['bootstrapData'] = $bootstrapData->getEncoded();
        }

        return response()->json($response);
    }
}
