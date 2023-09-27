<?php namespace Common\Auth\Controllers;

use App\User;
use Auth;
use Common\Auth\Actions\PaginateUsers;
use Common\Auth\Requests\ModifyUsers;
use Common\Auth\UserRepository;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct(
        protected User $user,
        protected UserRepository $userRepository,
        protected Request $request,
        protected Settings $settings,
    ) {
        $this->middleware('auth', ['except' => ['show']]);
    }

    public function index()
    {
        $this->authorize('index', User::class);

        $pagination = app(PaginateUsers::class)->execute($this->request->all());

        return $this->success(['pagination' => $pagination]);
    }

    public function show(User $user)
    {
        $relations = array_filter(
            explode(',', $this->request->get('with', '')),
        );
        $relations = array_merge(['roles', 'social_profiles'], $relations);

        if ($this->settings->get('envato.enable')) {
            $relations[] = 'purchase_codes';
        }
        
        if (Auth::id() === $user->id) {
            $relations[] = 'tokens';
            $user->makeVisible([
              'two_factor_confirmed_at',
              'two_factor_recovery_codes',
            ]);
            if ($user->two_factor_confirmed_at) {
                $user->two_factor_recovery_codes = $user->recoveryCodes();
                $user->syncOriginal();
            }
        }

        $user->load($relations);

        $this->authorize('show', $user);

        return $this->success(['user' => $user]);
    }

    public function store(ModifyUsers $request)
    {
        $this->authorize('store', User::class);

        $user = $this->userRepository->create($request->all());

        return $this->success(['user' => $user], 201);
    }

    public function update(User $user, ModifyUsers $request)
    {
        $this->authorize('update', $user);

        $user = $this->userRepository->update($user, $request->all());

        return $this->success(['user' => $user]);
    }

    public function destroy(string $ids)
    {
        $userIds = explode(',', $ids);
        $shouldDeleteCurrentUser = $this->request->get('deleteCurrentUser');
        $this->authorize('destroy', [User::class, $userIds]);

        $users = $this->user->whereIn('id', $userIds)->get();

        // guard against current user or admin user deletion
        foreach ($users as $user) {
            if (!$shouldDeleteCurrentUser && $user->id === Auth::id()) {
                return $this->error(
                    __('Could not delete currently logged in user: :email', [
                        'email' => $user->email,
                    ]),
                );
            }

            if ($user->hasPermission('admin')) {
                return $this->error(
                    __('Could not delete admin user: :email', [
                        'email' => $user->email,
                    ]),
                );
            }
        }

        $this->userRepository->deleteMultiple($users->pluck('id'));

        return $this->success();
    }
}
