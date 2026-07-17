<?php

namespace Collector;

use Collector\Models\Subscription;
use RuntimeException;

/**
 * Fluent builder for starting a subscription through PayStack's hosted checkout,
 * modelled on Laravel Cashier's `newSubscription(...)->checkout(...)` API.
 */
class SubscriptionBuilder
{
    protected ?int $trialDays = null;

    protected int $quantity = 1;

    protected array $metadata = [];

    public function __construct(
        protected $owner,
        protected string $name,
        protected string $plan,
    ) {}

    /**
     * Number of trial days to record on the resulting subscription.
     */
    public function trialDays(int $days): static
    {
        $this->trialDays = $days;

        return $this;
    }

    public function quantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function withMetadata(array $metadata): static
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    /**
     * Cancel any existing active subscription (plan switching), ensure the
     * PayStack customer exists, and start a hosted checkout for the plan.
     *
     * Accepts Cashier-style options: `success_url` maps to PayStack's
     * `callback_url`. Returns a Checkout you can return from a controller.
     */
    public function checkout(array $options = []): Checkout
    {
        $this->owner->subscriptions()
            ->where('paystack_status', Subscription::ACTIVE_STATUS)
            ->get()
            ->each(fn(Subscription $subscription) => $subscription->cancel());

        $customer = $this->owner->createOrGetPayStackCustomer(['email' => $this->owner->email]);

        $url = $this->owner->initiateTransaction($customer, $this->plan, $this->transactionPayload($options));

        if (! $url) {
            throw new RuntimeException('Unable to start PayStack checkout for plan [' . $this->plan . '].');
        }

        return new Checkout($url);
    }

    protected function transactionPayload(array $options): array
    {
        $payload = [];

        if (isset($options['success_url'])) {
            $payload['callback_url'] = $options['success_url'];
        }

        $payload['metadata'] = array_merge($this->metadata, array_filter([
            'subscription_name' => $this->name,
            'quantity' => $this->quantity,
            'trial_days' => $this->trialDays,
        ], fn($value) => ! is_null($value)));

        return $payload;
    }
}
