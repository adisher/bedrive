<?php

namespace Common\Workspaces\Actions;

use Auth;
use Common\Workspaces\Workspace;

class CrupdateWorkspace
{
    public function __construct(protected Workspace $workspace)
    {
    }

    public function execute(
        array $data,
        Workspace|null $initialWorkspace = null
    ): Workspace {
        if ($initialWorkspace) {
            $workspace = $initialWorkspace;
        } else {
            $workspace = $this->workspace->newInstance([
                'owner_id' => Auth::id(),
            ]);
        }

        $attributes = [
            'name' => $data['name'],
        ];

        $workspace->fill($attributes)->save();

        if (!$initialWorkspace) {
            $workspace
                ->members()
                ->create(['user_id' => Auth::id(), 'is_owner' => true]);
        }

        return $workspace;
    }
}
