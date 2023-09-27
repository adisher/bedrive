<?php

namespace App\Listeners;

use App\ShareableLink;
use Common\Files\Events\FileEntriesDeleted;

class DeleteShareableLinks
{
    public function __construct(private ShareableLink $link)
    {
    }

    /**
     * @return void
     */
    public function handle(FileEntriesDeleted $event)
    {
        if ($event->permanently) {
            $this->link->whereIn('entry_id', $event->entryIds)->delete();
        }
    }
}
