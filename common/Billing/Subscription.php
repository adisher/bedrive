<?php namespace Common\Billing;

use App\User;
use Carbon\Carbon;
use Common\Billing\Gateways\Contracts\CommonSubscriptionGatewayActions;
use Common\Billing\Gateways\Paypal\Paypal;
use Common\Billing\Gateways\Stripe\Stripe;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscriptions\SubscriptionFactory;
use Common\Search\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class Subscription extends Model
{
    use HasFactory, Searchable;

    protected $guarded = ['id'];

    protected $appends = [
        'on_grace_period',
        'on_trial',
        'valid',
        'active',
        'cancelled',
    ];

    protected $casts = [
        'id' => 'integer',
        'price_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function getOnGracePeriodAttribute(): bool
    {
        return $this->onGracePeriod();
    }

    public function getOnTrialAttribute(): bool
    {
        return $this->onTrial();
    }

    public function getValidAttribute(): bool
    {
        return $this->valid();
    }

    public function getActiveAttribute(): bool
    {
        return $this->active();
    }

    public function getCancelledAttribute(): bool
    {
        return $this->cancelled();
    }
    protected $dates = [
        'trial_ends_at',
        'ends_at',
        'renews_at',
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function onTrial(): bool
    {
        if (!is_null($this->trial_ends_at)) {
            return Carbon::now()->lt($this->trial_ends_at);
        } else {
            return false;
        }
    }

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     */
    public function valid(): bool
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    public function active(): bool
    {
        return is_null($this->ends_at) || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is no longer active.
     */
    public function cancelled(): bool
    {
        return !is_null($this->ends_at);
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     */
    public function onGracePeriod(): bool
    {
        if (!is_null($endsAt = $this->ends_at)) {
            return Carbon::now()->lt(Carbon::instance($endsAt));
        } else {
            return false;
        }
    }

    public function changePlan(Product $newProduct, Price $newPrice): self
    {
        $isSuccess = $this->gateway()->changePlan(
            $this,
            $newProduct,
            $newPrice,
        );

        if ($isSuccess) {
            $this->fill([
                'product_id' => $newProduct->id,
                'price_id' => $newPrice->id,
                'ends_at' => null,
            ])->save();
        }

        return $this;
    }

    public function cancel(bool $atPeriodEnd = true): self
    {
        if ($this->gateway_name !== 'none') {
            $this->gateway()->cancelSubscription($this, $atPeriodEnd);
        }

        // If the user was on trial, we will set the grace period to end when the trial
        // would have ended. Otherwise, we'll retrieve the end of the billing period
        // and make that the end of the grace period for this current user.
        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            $this->ends_at = $this->renews_at;
        }

        $this->renews_at = null;
        $this->save();

        return $this;
    }

    /**
     * Mark subscription as cancelled on local database
     * only, without interacting with payment gateway.
     */
    public function markAsCancelled(): void
    {
        $this->fill([
            'ends_at' => $this->renews_at,
            'renews_at' => null,
        ])->save();
    }

    /**
     * Cancel the subscription immediately and delete it from database.
     */
    public function cancelAndDelete(): self
    {
        $this->cancel(false);
        $this->delete();

        $this->user->update([
            'card_last_four' => null,
            'card_brand' => null,
            'card_expires' => null,
        ]);

        return $this;
    }

    public function resume(): self
    {
        if (!$this->onGracePeriod()) {
            throw new LogicException(
                __(
                    'Unable to resume subscription that is not within grace period.',
                ),
            );
        }

        if ($this->onTrial()) {
            $trialEnd = $this->trial_ends_at->getTimestamp();
        } else {
            $trialEnd = 'now';
        }

        // To resume the subscription we need to set the plan parameter on the Stripe
        // subscription object. This will force Stripe to resume this subscription
        // where we left off. Then, we'll set the proper trial ending timestamp.
        if ($this->gateway_name !== 'none') {
            $this->gateway()->resumeSubscription($this, [
                'trial_end' => $trialEnd,
            ]);
        }

        // Finally, we will remove the ending timestamp from the user's record in the
        // local database to indicate that the subscription is active again and is
        // no longer "cancelled". Then we will save this record in the database.
        $this->renews_at = $this->ends_at;
        $this->ends_at = null;
        $this->save();

        return $this;
    }

    /**
     * Get gateway this subscription was created with.
     */
    public function gateway(): ?CommonSubscriptionGatewayActions
    {
        if ($this->gateway_name === 'stripe') {
            return app(Stripe::class);
        } elseif ($this->gateway_name === 'paypal') {
            return app(Paypal::class);
        }

        return null;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'price_id' => $this->price_id,
            'gateway_name' => $this->gateway_name,
            'user' => $this->user ? $this->user->getSearchableValues() : null,
            'description' => $this->description,
            'ends_at' => $this->ends_at,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['user']);
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'product_id',
            'price_id',
            'gateway_name',
            'ends_at',
            'created_at',
            'updated_at',
        ];
    }

    protected static function newFactory()
    {
        return SubscriptionFactory::new();
    }
}
