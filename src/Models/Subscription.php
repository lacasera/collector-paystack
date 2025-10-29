<?php

namespace Collector\Models;

use Carbon\Carbon;
use Collector\Events\SubscriptionCanceled;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const ACTIVE_STATUS = 'active';

    public const TRIALING_STATUS = 'trialing';

    public const CANCELLED_STATUS = 'cancelled';

    public static string $subscriptionModel = self::class;

    public static string $customerModel = 'App\\Models\\User';

    /**
     * The attributes that are not mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
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
        $model = static::$customerModel;

        return $this->belongsTo($model, (new $model())->getForeignKey());
    }

    /**
     * Set the customer model class name.
     */
    public static function useCustomerModel(string $customerModel): void
    {
        static::$customerModel = $customerModel;
    }

    /**
     * Set the subscription model class name.
     */
    public static function useSubscriptionModel(string $subscriptionModel): void
    {
        static::$subscriptionModel = $subscriptionModel;
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE_STATUS || ($this->ends_at && $this->ends_at->isFuture());
    }

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     */
    public function valid(): bool
    {
        return $this->isActive() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is within its trial period.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the subscription's trial has expired.
     */
    public function hasExpiredTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Filter query by expired trial.
     */
    public function scopeExpiredTrial(Builder $query): void
    {
        $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '<', Carbon::now());
    }

    /**
     * Filter query by not on trial.
     */
    public function scopeNotOnTrial(Builder $query): void
    {
        $query->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', Carbon::now());
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     */
    public function onGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Filter query by on grace period.
     */
    public function scopeOnGracePeriod(Builder $query): void
    {
        $query->whereNotNull('ends_at')->where('ends_at', '>', Carbon::now());
    }

    /**
     * Filter query by not on grace period.
     */
    public function scopeNotOnGracePeriod(Builder $query): void
    {
        $query->whereNull('ends_at')->orWhere('ends_at', '<=', Carbon::now());
    }

    /**
     * Mark the subscription as canceled.
     */
    public function markAsCanceled(): void
    {
        $this->fill([
            'paystack_status' => self::CANCELLED_STATUS,
            'ends_at' => $this->getEndingDate(),
        ])->save();
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(?string $reason = null): bool
    {
        if ($this->owner->cancelOnPayStack($this)) {
            $this->markAsCanceled();
            SubscriptionCanceled::dispatch($this->owner, $this);

            return $this->fill(['cancelation_reason' => $reason])->save();
        }

        return false;
    }

    public function swap(): void
    {
        // Implementation for plan swapping
    }

    private function getEndingDate(): Carbon
    {
        $subscription = $this->owner->fetchSubscription($this->paystack_id);

        if (! $subscription) {
            return now();
        }

        return Carbon::parse(data_get($subscription, 'most_recent_invoice.period_end'));
    }
}
