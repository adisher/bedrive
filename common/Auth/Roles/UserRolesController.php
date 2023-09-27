<?php namespace Common\Auth\Roles;

use App\User;
use Common\Auth\UserRepository;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class UserRolesController extends BaseController
{
    /**
     * UserRepository instance.
     *
     * @var UserRepository
     */
    private $repository;

    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * UserRolesController constructor.
     *
     * @param UserRepository $repository
     * @param Request $request
     */
    public function __construct(UserRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request = $request;
    }

    /**
     * Attach specified roles to user.
     *
     * @param int $userId
     * @return int
     */
    public function attach($userId)
    {
        $user = User::findOrFail($userId);

        $this->authorize('update', $user);

        return $this->repository->attachRoles(
            $user,
            $this->request->get('roles'),
            'attach',
        );
    }

    /**
     * Detach specified roles from user.
     *
     * @param int $userId
     * @return int
     */
    public function detach($userId)
    {
        $user = User::findOrFail($userId);

        $this->authorize('update', $user);

        return $user->roles()->detach($this->request->get('roles'));
    }
}
