<?php

namespace App\Policies;

use App\Services\Links\ValidatesLinkPassword;
use App\ShareableLink;
use App\User;
use Common\Core\Policies\FileEntryPolicy;
use Common\Files\FileEntry;
use Common\Settings\Settings;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DriveFileEntryPolicy extends FileEntryPolicy
{
    use ValidatesLinkPassword;

    public function __construct(
        protected Request $request,
        protected Settings $settings,
        protected ActiveWorkspace $activeWorkspace,
    ) {
        parent::__construct($this->request, $this->settings);
    }

    public function index(
        ?User $currentUser,
        array $entryIds = null,
        int $userId = null,
    ): bool {
        // if we're requesting resources for a particular workspace, let user view the resources
        // as long as they are a member, even without explicit "files.view" permission
        if (!$entryIds && !$this->activeWorkspace->isPersonal()) {
            return (bool) $this->activeWorkspace->member($currentUser->id);
        }

        return parent::index($currentUser, $entryIds, $userId);
    }

    public function show(
        ?User $user,
        FileEntry $entry,
        ShareableLink $link = null,
    ): bool {
        if (($link = $this->getLinkForRequest($link)) !== null) {
            return $this->authorizeShareableLink($link, $entry);
        }

        return parent::show($user, $entry);
    }

    public function download(
        User $user,
        $entries,
        ShareableLink $link = null,
    ): bool {
        if (($link = $this->getLinkForRequest($link)) !== null) {
            // shareable link is always for one entry only
            return $this->authorizeShareableLink($link, $entries[0]);
        }

        return parent::download($user, $entries);
    }

    protected function userCan(User $currentUser, string $permission, $entries)
    {
        $entries = $this->findEntries($entries);

        // first run regular checks (user has global permission, or owns entry)
        if (parent::userCan($currentUser, $permission, $entries)) {
            return true;

            // if we're not in personal workspace, check if user has permissions via workspace
        } elseif (!$this->activeWorkspace->isPersonal()) {
            // first check if user is a member of active workspace
            if (
                ($workspaceMember = $this->activeWorkspace->member(
                    $currentUser->id,
                )) !== null
            ) {
                // then check if user has specified permission for all the entries
                return $entries->every(function (FileEntry $entry) use (
                    $permission,
                    $workspaceMember,
                ) {
                    $entryIsInWorkspace =
                        $entry->workspace_id === $this->activeWorkspace->id;
                    // file entry listing will be restricted in the builder query, no need to error here if user has no permission
                    if ($permission === 'files.view') {
                        return $entryIsInWorkspace;
                    } else {
                        return $entryIsInWorkspace &&
                            $workspaceMember->hasPermission($permission);
                    }
                });
            }
        }

        return false;
    }

    private function authorizeShareableLink(
        ShareableLink $link,
        FileEntry $entry,
    ): bool {
        // check password first, if needed
        if (!$this->passwordIsValid($link)) {
            return false;
        }

        // user can view this file if file or any of its parents is attached to specified link
        $entryPath = explode('/', $entry->path);
        $link = Arr::first(
            $entryPath,
            fn($entryId) => (int) $entryId === $link->entry_id,
        );

        return $link ?? false;
    }

    private function getLinkForRequest(
        ShareableLink $link = null,
    ): ?ShareableLink {
        if ($link !== null) {
            return $link;
        }

        if ($this->request->has('shareable_link')) {
            $linkId = $this->request->get('shareable_link');
            return app(ShareableLink::class)->findOrFail($linkId);
        }

        return null;
    }
}
