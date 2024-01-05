<?php

namespace Collector\Models;

use Carbon\Carbon;
use Collector\Events\SubscriptionCanceled;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const ACTIVE_STATUS = 'active';

    public const TRIALING_STATUS = 'trialing';

    public const CANCELLED_STATUS = 'cancelled';

    /**
     * @var string
     */
    public static string $subscriptionModel = Subscription::class;

    /**
     * @var string
     */
    public static string $customerModel = 'App\\Models\\User';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ends_at' => 'datetime',
        'quantity' => 'integer',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->owner();
    }

    /**
     * Get the model related to the subscription.
     */
    public function owner(): BelongsTo
    {
        $model = self::$customerModel;

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    /**
     * Set the customer model class name.
     *
     * @param  string  $customerModel
     * @return void
     */
    public static function useCustomerModel($customerModel)
    {
        static::$customerModel = $customerModel;
    }

    /**
     * Set the subscription model class name.
     *
     * @param  string  $subscriptionModel
     * @return void
     */
    public static function useSubscriptionModel($subscriptionModel)
    {
        static::$subscriptionModel = $subscriptionModel;
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE_STATUS || ($this->ends_at && $this->ends_at->isFuture());
    }

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->isActive() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the subscription's trial has expired.
     *
     * @return bool
     */
    public function hasExpiredTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Filter query by expired trial.
     *
     * @return void
     */
    public function scopeExpiredTrial(Builder $query)
    {
        $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '<', Carbon::now());
    }

    /**
     * Filter query by not on trial.
     *
     * @return void
     */
    public function scopeNotOnTrial(Builder $query)
    {
        $query->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', Carbon::now());
    }

    /**
     * Determine if the subscription is within its grace period after cancelation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Filter query by on grace period.
     *
     * @return void
     */
    public function scopeOnGracePeriod(Builder $query)
    {
        $query->whereNotNull('ends_at')->where('ends_at', '>', Carbon::now());
    }

    /**
     * Filter query by not on grace period.
     *
     * @return void
     */
    public function scopeNotOnGracePeriod(Builder $query)
    {
        $query->whereNull('ends_at')->orWhere('ends_at', '<=', Carbon::now());
    }

    /**
     * @return void
     */
    public function markAsCanceled($cancelAt = null)
    {
        $this->fill([
            'paystack_status' => self::CANCELLED_STATUS,
            'ends_at' => $cancelAt ?? $this->getEndingDate(),
        ])->save();
    }

    /**
     * @return bool
     */
    public function cancel(string $reason)
    {
        if ($this->owner->cancelOnPayStack($this)) {
            $this->markAsCanceled();
            SubscriptionCanceled::dispatch($this->owner, $this);

            return $this->fill(['cancelation_reason' => $reason])->save();
        }
        return false;
    }

    public function cancelNow()
    {
        if($this->owner->cancelOnPayStack($this)) {
            $this->markAsCanceled(now());

            return true;
        }

        return false;
    }

    public function swap()
    {

    }

    /**
     * @return Carbon
     */
    public function getNextBillingDate(): Carbon
    {
        $subscription = $this->owner->fetchSubscription($this->paystack_id);

        if (!$subscription) {
            return now();
        }

        return Carbon::parse(data_get($subscription, 'next_payment_date'));
    }

    /**
     * @return Carbon
     */
    private function getEndingDate(): Carbon
    {
        $subscription = $this->owner->fetchSubscription($this->paystack_id);

        if (! $subscription) {
            return now();
        }

        return Carbon::parse(data_get($subscription, 'most_recent_invoice.period_end'));
    }
}
