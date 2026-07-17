<?php

namespace Collector\Actions;

use Collector\Concerns\CreateSubscription;

class CreateSubscriptions implements CreateSubscription
{
    /**
     * Start a checkout for the plan and return the PayStack redirect URL.
     *
     * Delegates to the fluent subscription builder, which cancels any existing
     * active subscription (plan switching) and initiates the hosted checkout.
     */
    public function create($collectable, $plan, $options = [])
    {
        return (string) $collectable->newSubscription('default', $plan)->checkout($options);
    }
}
