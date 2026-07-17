<?php

namespace Collector\Actions;

use Collector\Collector;
use Collector\Concerns\CreateSubscription;
use Collector\Models\Subscription;
use Collector\Plan;

class CreateSubscriptions implements CreateSubscription
{
    public function create($collectable, $plan, $options = [])
    {
        $type = $collectable->collectorConfiguration('type');

        /** @var Plan $paystackPlan */
        $payStackPlan = Collector::plans($type)->where('id', $plan)->first();

        $this->cancelExistingSubscriptions($collectable);

        $customer = $collectable->createOrGetPayStackCustomer(['email' => $collectable->email]);

        if (! $customer) {
            return null;
        }

        return $collectable->initiateTransaction($customer, $payStackPlan->id);
    }

    /**
     * Cancel the collectable's currently active subscriptions on PayStack before
     * a new one is started, so switching plans does not leave the old plan
     * billing in parallel.
     */
    protected function cancelExistingSubscriptions($collectable): void
    {
        $collectable->subscriptions()
            ->where('paystack_status', Subscription::ACTIVE_STATUS)
            ->get()
            ->each(fn(Subscription $subscription) => $subscription->cancel());
    }
}
