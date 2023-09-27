<?php

namespace Common\Auth\Controllers;

use App\User;
use Common\Core\BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class BanController extends BaseController
{
    public function store(User $user)
    {
        $this->authorize('destroy', [User::class, [$user->id]]);

        if ($user->hasPermission('admin')) {
            abort(403, 'Admin users can\'t be suspended');
        }

        $data = $this->validate(request(), [
            'ban_until' => 'nullable|date|after:now',
            'comment' => 'nullable|string|max:255',
            'permanent' => 'boolean',
        ]);

        $user->bans()->create([
            'expired_at' => $data['permanent']
                ? null
                : Arr::get($data, 'ban_until'),
            'comment' => Arr::get($data, 'comment'),
            'created_by_type' => User::class,
            'created_by_id' => Auth::id(),
        ]);
        $user->fill(['banned_at' => now()])->save();

        return $this->success(['user' => $user]);
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', [User::class, [$user->id]]);

        $user->bans()->delete();
        $user->fill(['banned_at' => null])->save();

        return $this->success(['user' => $user]);
    }
}
