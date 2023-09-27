<?php namespace Common\Core;

use App\User;
use Common\Core\Prerender\HandlesSeo;
use Illuminate\Auth\Access\Response as AuthResponse;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, HandlesSeo;

    // todo: refactor bedrive and belink policies to use basePolicy permission check and remove guest fetching here

    /**
     * Authorize a given action for the current user
     * or guest if user is not logged in.
     */
    public function authorize(
        string $ability,
        mixed $arguments = [],
    ): AuthResponse {
        if (Auth::check()) {
            [$ability, $arguments] = $this->parseAbilityAndArguments(
                $ability,
                $arguments,
            );
            return app(Gate::class)->authorize($ability, $arguments);
        } else {
            $guest = new User();
            // make sure ID is not NULL to avoid false positives in authorization
            $guest->forceFill(['id' => -1]);
            $guest->setRelation('roles', app('guestRole'));
            return $this->authorizeForUser($guest, $ability, $arguments);
        }
    }

    public function success(
        array|Collection $data = [],
        int $status = 200,
        array $options = [],
    ) {
        $data = $data ?: [];
        if (!Arr::get($data, 'status')) {
            $data['status'] = 'success';
        }

        // only generate seo tags if request is coming from frontend and not from API
        if (
            (requestIsFromFrontend() || defined('SHOULD_PRERENDER')) &&
            ($response = $this->handleSeo($data, $options))
        ) {
            return $response;
        }

        foreach ($data as $key => $value) {
            if ($value instanceof Arrayable) {
                $data[$key] = $value->toArray();
            }
        }

        return response()->json($data, $status);
    }

    /**
     * Return error response with specified messages.
     */
    public function error(
        ?string $message = '',
        array $errors = [],
        int $status = 422,
        $data = [],
    ) {
        $data = array_merge($data, [
            'message' => $message,
            'errors' => $errors ?: [],
        ]);
        return response()->json($data, $status);
    }
}
