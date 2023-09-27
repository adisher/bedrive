<?php

namespace Common\Billing\Invoices;

use Common\Billing\Subscription;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Invoice
 *
 * @property-read Subscription $subscription
 * @mixin Eloquent
 * @property int $id
 * @property int $subscription_id
 * @property int $paid
 * @property string $uuid
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Invoice newModelQuery()
 * @method static Builder|Invoice newQuery()
 * @method static Builder|Invoice query()
 * @method static Builder|Invoice whereCreatedAt($value)
 * @method static Builder|Invoice whereId($value)
 * @method static Builder|Invoice whereNotes($value)
 * @method static Builder|Invoice wherePaid($value)
 * @method static Builder|Invoice whereSubscriptionId($value)
 * @method static Builder|Invoice whereUpdatedAt($value)
 * @method static Builder|Invoice whereUuid($value)
 */
class Invoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'subscription_id' => 'integer',
        'paid' => 'boolean',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
