<?php namespace Common\Files\Actions;

use App\User;
use Common\Billing\Models\Product;
use Common\Settings\Settings;
use Illuminate\Support\Facades\Auth;

class GetUserSpaceUsage
{
    protected User $user;

    public function __construct(protected Settings $settings)
    {
        $this->user = Auth::user();
    }

    public function execute(User $user = null): array
    {
        $this->user = $user ?? Auth::user();
        return [
            'used' => $this->getSpaceUsed(),
            'available' => $this->getAvailableSpace(),
        ];
    }

    private function getSpaceUsed(): int|float
    {
        return (int) $this->user
            ->entries(['owner' => true])
            ->where('type', '!=', 'folder')
            ->withTrashed()
            ->sum('file_size');
    }

    public function getAvailableSpace(): int|float|null
    {
        $space = null;

        if (!is_null($this->user->available_space)) {
            $space = $this->user->available_space;
        } elseif (app(Settings::class)->get('billing.enable')) {
            if ($this->user->subscribed()) {
                $space = $this->user->subscriptions->first()->product
                    ->available_space;
            } elseif ($freePlan = Product::where('free', true)->first()) {
                $space = $freePlan->available_space;
            }
        }

        // space is not set at all on user or billing plans
        if (is_null($space)) {
            $defaultSpace = $this->settings->get('uploads.available_space');
            return is_numeric($defaultSpace) ? abs($defaultSpace) : null;
        } else {
            return abs($space);
        }
    }

    public function hasEnoughSpaceToUpload(int $bytes): bool
    {
        $availableSpace = $this->getAvailableSpace();

        // unlimited space
        if (is_null($availableSpace)) {
            return true;
        }
        return $this->getSpaceUsed() + $bytes <= $availableSpace;
    }
}
