<?php

namespace Collector\Actions;

use Collector\Collectable;
use Collector\Collector;
use Collector\Concerns\CreateSubscription;
use Collector\Plan;

class CreateSubscriptions implements CreateSubscription
{
    public function create(Collectable $collectable, $plan, $options = [])
    {
        $type = $collectable->collectorConfiguration('type');

        /** @var Plan $paystackPlan */
        $paystackPlan = Collector::plans($type)->where('id', $plan)->first();

        $this->cancelExistingSubscriptions($collectable);

        $subscriptionBuilder = $collectable->newSubscription($paystackPlan);
    }

    protected function cancelExistingSubscriptions(Collectable $collectable)
    {

    }
}