<?php

namespace Common\Core\Exceptions;

use ErrorException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Sentry\Laravel\Integration;
use Sentry\State\Scope;
use Throwable;
use function Sentry\configureScope;

class BaseExceptionHandler extends Handler
{
    public function register()
    {
        if (config('app.env') !== 'production') {
            return;
        }

        $this->renderable(function (ErrorException $e) {
            if (
                Str::contains($e->getMessage(), [
                    'failed to open stream: Permission denied',
                    'mkdir(): Permission denied',
                ])
            ) {
                return $this->filePermissionResponse($e);
            }
        });

        configureScope(function (Scope $scope): void {
            if ($user = Auth::user()) {
                $scope->setUser(['email' => $user->email, 'id' => $user->id]);
            }
        });

        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    protected function convertExceptionToArray(Throwable $e): array
    {
        $array = parent::convertExceptionToArray($e);
        $previous = $e->getPrevious();

        if (
            $previous &&
            method_exists($previous, 'response') &&
            property_exists($previous->response(), 'action')
        ) {
            $array['action'] = $e->getPrevious()->response()->action;
        }

        if ($array['message'] === 'Server Error') {
            $array['message'] = __(
                'There was an issue. Please try again later.',
            );
        }

        if ($array['message'] === 'This action is unauthorized.') {
            $array['message'] = __(
                "You don't have required permissions for this action.",
            );
        }

        return $array;
    }

    protected function filePermissionResponse(ErrorException $e)
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => 'test']);
        } else {
            preg_match('/\((.+?)\):/', $e->getMessage(), $matches);
            $path = $matches[1] ?? null;
            // should not return a view here, in case laravel views folder is not readable as well
            return response(
                "<div style='text-align:center'><h1>Could not access a file or folder</h1> <br> Location: <b>$path</b><br>" .
                    '<p>See the article here for possible solutions: <a target="_blank" href="https://support.vebto.com/help-center/articles/21/25/207/changing-file-permissions">https://support.vebto.com/help-center/articles/207/changing-file-permissions</a></p></div>',
            );
        }
    }
}
