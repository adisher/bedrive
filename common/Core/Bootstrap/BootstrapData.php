<?php

namespace Common\Core\Bootstrap;

interface BootstrapData
{
    public function getEncoded(): string;

    public function init(): self;

    public function getThemes(): array;
}
