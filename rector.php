<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Laravel\Set\LaravelSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/common'
    ]);

    $rectorConfig->skip([
      __DIR__ . '/vendor',
      __DIR__ . '/storage',
      __DIR__ . '/tests',
      __DIR__ . '/bootstrap',
    ]);

  $rectorConfig->sets([
    levelSetList::UP_TO_PHP_80,
    LaravelSetList::LARAVEL_90,
  ]);
};
