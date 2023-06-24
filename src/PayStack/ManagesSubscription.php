<?php

namespace Collector\PayStack;

use Collector\Plan;
use Collector\SubscriptionBuilder;

trait ManagesSubscription
{
    public function create()
    {
        /**
         * check if the user has an existing subscription
         * create a customer in paystack
         * create a transaction if this the user's first
         *
         */
    }

    public function newSubscription(Plan $plan, $prices = [])
    {
        //$customer = $this->getPayStackCustomer();

        return new SubscriptionBuilder($this, $plan->name, $prices);
    }
}
