<?php

namespace App\Services;

use App\RootFolder;
use App\Services\Entries\SetPermissionsOnEntry;
use Common\Core\Bootstrap\BaseBootstrapData;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Support\Facades\Cookie;

class AppBootstrapData extends BaseBootstrapData
{
    public function init(): self
    {
        parent::init();

        // need to fetch workspaceId from cookie as there will be no request from client at this point yet
        $workspaceId = (int) Cookie::get('activeWorkspaceId');
        app(ActiveWorkspace::class)->id = $workspaceId;
        $this->data['rootFolder'] = app(SetPermissionsOnEntry::class)->execute(
            new RootFolder(),
        );
        app(ActiveWorkspace::class)->id = 0;

        return $this;
    }
}
