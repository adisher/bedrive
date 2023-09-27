<?php namespace Common\Auth\Controllers;

use App\User;
use Common\Auth\Events\UserAvatarChanged;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected User $user,
    ) {
    }

    public function store(User $user)
    {
        $this->authorize('update', $user);

        $this->validate($this->request, [
            'file' => 'required_without:url|image|max:1500',
            'url' => 'required_without:file|string|max:250',
        ]);

        // delete old user avatar
        if ($user->getRawOriginal('avatar')) {
            Storage::disk('public')->delete($user->getRawOriginal('avatar'));
        }

        // store new avatar on public disk
        $path =
            $this->request->get('url') ??
            $this->request
                ->file('file')
                ->storePublicly('avatars', ['disk' => 'public']);

        // attach avatar to user model
        $user->avatar = $path;
        $user->save();

        event(new UserAvatarChanged($user));

        return $this->success(['user' => $user]);
    }

    public function destroy(User $user)
    {
        $this->authorize('update', $user);

        if ($user->getRawOriginal('avatar')) {
            Storage::disk('public')->delete($user->getRawOriginal('avatar'));
        }

        $user->avatar = null;
        $user->save();

        event(new UserAvatarChanged($user));

        return $this->success();
    }
}
