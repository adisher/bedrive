<?php

namespace Common\Settings\Validators;

use Common\Settings\DotEnvEditor;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class CacheConfigValidator
{
    const KEYS = ['cache_driver'];

    public function fails($settings)
    {
        $this->setConfigDynamically($settings);

        try {
            $driverName = Arr::get(
                $settings,
                'cache_driver',
                config('cache.default'),
            );
            $driver = Cache::driver($driverName);
            $driver->put('foo', 'bar', 1);
            if ($driver->get('foo') !== 'bar') {
                return $this->getDefaultErrorMessage();
            }
        } catch (Exception $e) {
            return $this->getErrorMessage($e);
        } catch (Throwable $e) {
            return $this->getErrorMessage($e);
        }
    }

    private function setConfigDynamically($settings)
    {
        app(DotEnvEditor::class)->write(
            Arr::except($settings, ['cache_driver']),
        );
    }

    private function getErrorMessage($e): array
    {
        $message = $e->getMessage();

        if (Str::contains($message, 'apc_fetch')) {
            return ['cache_group' => "Could not enable APC. $message"];
        } elseif (Str::contains($message, 'Memcached')) {
            return ['cache_group' => "Could not enable Memcached. $message"];
        } elseif (Str::contains($message, 'Connection refused')) {
            return ['cache_group' => 'Could not connect to redis server.'];
        } else {
            return $this->getDefaultErrorMessage();
        }
    }

    private function getDefaultErrorMessage(): array
    {
        return ['cache_group' => 'Could not enable this cache method.'];
    }
}
