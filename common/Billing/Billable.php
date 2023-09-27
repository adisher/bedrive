<?php namespace Common\Billing;

use Carbon\Carbon;
use Common\Billing\Models\Price;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

/**
 * Trait Billable
 * @property-read Collection|Subscription[] $subscriptions
 */
trait Billable
{
    public function subscribe(string $gateway, string $gatewayId, Price $price): Subscription
    {
        if (Subscription::where('gateway_id', $gatewayId)->exists()) {
            throw new LogicException(__('This subscription ID already exists'));
        }

        if ($price->interval === 'year') {
            $renewsAt = Carbon::now()->addYears($price->interval_count);
        } elseif ($price->interval === 'week') {
            $renewsAt = Carbon::now()->addWeeks($price->interval_count);
        } else {
            $renewsAt = Carbon::now()->addMonths($price->interval_count);
        }

        $subscription = $this->subscriptions()->create([
            'price_id' => $price->id,
            'product_id' => $price->product_id,
            'ends_at' => null,
            'renews_at' => $renewsAt,
            'gateway_name' => $gateway,
            'gateway_id' => $gatewayId,
        ]);

        $this->load('subscriptions');

        return $subscription;
    }

    public function subscribed(): bool
    {
        $subscription = $this->subscriptions->first(function (
            Subscription $sub
        ) {
            return $sub->valid();
        });

        return !is_null($subscription);
    }

    public function subscriptions(): HasMany
    {
        // always return subscriptions that are not attached to any gateway last
        return $this->hasMany(Subscription::class, 'user_id')->orderBy(
            DB::raw('FIELD(gateway_name, "none")'),
            'asc',
        );
    }
}
