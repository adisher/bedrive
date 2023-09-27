<?php

namespace Common\Files\Events;

use Common\Files\FileEntry;

class FileEntryCreated
{
    /**
     * @var FileEntry
     */
    public $fileEntry;

    public function __construct(FileEntry $fileEntry)
    {
        $this->fileEntry = $fileEntry;
    }
}
