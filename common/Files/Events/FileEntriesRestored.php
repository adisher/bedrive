<?php

namespace Common\Files\Events;

class FileEntriesRestored
{
    public function __construct(public array $entryIds)
    {
    }
}
