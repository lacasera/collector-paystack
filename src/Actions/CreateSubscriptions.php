<?php

namespace Collector\Actions;

use Collector\Collector;
use Collector\Concerns\CreateSubscription;
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

    protected function cancelExistingSubscriptions($collectable) {}
}
