<?php

namespace App;

use Arr;
use Auth;
use Common\Workspaces\ActiveWorkspace;

class RootFolder extends FileEntry
{
    protected $id = 0;
    protected $appends = ['name'];
    protected $casts = [];
    protected $relations = ['users'];

    protected $attributes = [
        'type' => 'folder',
        'id' => 0,
        'hash' => '0',
        'path' => '',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setCurrentUserAsOwner();
        $this->workspace_id = app(ActiveWorkspace::class)->id;
    }

    public function getNameAttribute(): string
    {
        return trans('All Files');
    }

    public function getHashAttribute(): string
    {
        return '0';
    }

    private function setCurrentUserAsOwner(): void
    {
        $users = collect([]);
        if (
            Auth::check() &&
            app(ActiveWorkspace::class)->currentUserIsOwner()
        ) {
            $user = Arr::only(Auth::user()->toArray(), [
                'first_name',
                'last_name',
                'display_name',
                'email',
                'id',
                'avatar',
            ]);
            $user['owns_entry'] = true;
            $users[] = $user;
        }
        $this->users = $users;
    }
}
