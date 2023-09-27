<?php

namespace Common\Workspaces;

use App\User;
use Auth;

class ActiveWorkspace
{
    protected Workspace|null $cachedWorkspace = null;
    public array $memberCache = [];
    public int $id = 0;

    /**
     * Whether selected workspace was explicitly specified via request query params or defaulted to personal.
     */
    public bool $explicitlySelected = false;

    public function __construct()
    {
        $this->explicitlySelected = request()->has('workspaceId');
        $this->id = (int) request()->get('workspaceId', 0);
    }

    public function workspace(): ?Workspace
    {
        if (
            is_null($this->cachedWorkspace) ||
            $this->cachedWorkspace->id !== $this->id
        ) {
            $this->cachedWorkspace = $this->isPersonal()
                ? null
                : Workspace::find($this->id);
        }

        return $this->cachedWorkspace ?: null;
    }

    public function isPersonal(): bool
    {
        return !$this->id;
    }

    public function owner(): User
    {
        return $this->workspace()->owner_id === Auth::id()
            ? Auth::user()
            : $this->workspace()->owner;
    }

    public function currentUserIsOwner(): bool
    {
        if ($this->isPersonal()) {
            return true;
        }
        return $this->workspace() &&
            $this->workspace()->owner_id === Auth::id();
    }

    public function member(int $userId): ?WorkspaceMember
    {
        if (!$this->workspace()) {
            return null;
        }

        if (!isset($this->memberCache[$userId])) {
            $this->memberCache[$userId] = app(WorkspaceMember::class)
                ->where([
                    'user_id' => $userId,
                    'workspace_id' => $this->workspace()->id,
                ])
                ->first();
        }
        return $this->memberCache[$userId];
    }
}
