<?php

namespace Collector\Listeners;

use Collector\Actions\SyncSubscription;
use Collector\Events\PaymentVerified;

class SubscribeUserToPlan
{
    public function __construct(private readonly SyncSubscription $syncSubscription) {}

    /**
     * Handle the event.
     */
    public function handle(PaymentVerified $event): void
    {
        $collectable = $event->collectable;

        $transaction = $collectable->completedTransaction($event->reference);

        if (data_get($transaction, 'status') !== 'success') {
            return;
        }

       
        $attributes = [];

        // Without the customer code hasPayStackId() stays false and
        // SyncSubscription bails, so the subscription is never recorded.
        if (! $collectable->hasPayStackId() && $customerCode = data_get($transaction, 'customer.customer_code')) {
            $attributes['paystack_id'] = $customerCode;
        }

        if ($authorization = data_get($transaction, 'authorization')) {
            $attributes['pm_type'] = data_get($authorization, 'card_type');
            $attributes['pm_last_four'] = data_get($authorization, 'last4');
            $attributes['pm_expiration'] = data_get($authorization, 'exp_month') . '/' . data_get($authorization, 'exp_year');
        }

        if ($attributes) {
            $collectable->forceFill($attributes)->save();
        }

        $subscription = $this->syncSubscription->sync($collectable, data_get($transaction, 'plan_object.plan_code'));

        // Apply the trial requested at checkout (SubscriptionBuilder::trialDays),
        // round-tripped through the transaction metadata.
        $trialDays = (int) data_get($transaction, 'metadata.trial_days', 0);

        if ($subscription && $trialDays > 0 && is_null($subscription->trial_ends_at)) {
            $subscription->update(['trial_ends_at' => now()->addDays($trialDays)]);
        }
    }
}
