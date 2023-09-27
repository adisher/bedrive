<?php

namespace Common\Files\Tus;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class TusCache
{
    public function __construct()
    {
        if (
            config('cache.default') === 'array' ||
            config('cache.default') === 'null'
        ) {
            config()->set('cache.default', 'file');
        }
    }

    public function get(string $uploadKey)
    {
        return cache()->get("tus:$uploadKey");
    }

    public function set(
        string $uploadKey,
        array $value,
        $expiresAt = CarbonInterface::class,
    ): bool {
        return cache()->set("tus:$uploadKey", $value, $expiresAt);
    }

    public function merge(string $uploadKey, array $partialValue): bool
    {
        $value = $this->get($uploadKey);
        return cache()->set(
            "tus:$uploadKey",
            array_merge($value, $partialValue),
            Carbon::parse($value['expires_at']),
        );
    }

    public function delete(string $uploadKey): bool
    {
        return cache()->delete("tus:$uploadKey");
    }
}
