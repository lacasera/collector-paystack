<?php

namespace Collector\Actions;

use Collector\Collector;
use Collector\Concerns\CreateSubscription;
use Collector\Models\Subscription;
use Collector\Plan;
use Throwable;

class CreateSubscriptions implements CreateSubscription
{
    public function handle($collectable, $plan, $options = [])
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

    protected function cancelExistingSubscriptions($collectable)
    {
        $collectable->subscriptions()->where('paystack_status', '!=', Subscription::CANCELLED_STATUS)
            ->each(function ($subscription) {
                try {
                    $subscription->cancelNow();

                } catch (Throwable $e) {
                    //
                }
            });
    }
}
