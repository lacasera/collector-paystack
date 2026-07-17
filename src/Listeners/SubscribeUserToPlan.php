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

        $collectable->fill([
            'pm_type' => data_get($transaction, 'authorization.card_type'),
            'pm_last_four' => data_get($transaction, 'authorization.last4'),
            'pm_expiration' => data_get($transaction, 'authorization.exp_month') . '/' . data_get($transaction, 'authorization.exp_year'),
        ])->save();

        $this->syncSubscription->sync($collectable, data_get($transaction, 'plan_object.plan_code'));
    }
}
