<?php

namespace Collector\PayStack;

use Collector\Collector;
use Collector\Models\Subscription;
use Collector\SubscriptionBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait ManagesSubscription
{
    /**
     * Begin creating a new subscription (Cashier-style fluent builder).
     */
    public function newSubscription(string $name, string $plan): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $name, $plan);
    }

    /**
     * Determine if the model has an active subscription (optionally by name).
     */
    public function subscribed(?string $name = null): bool
    {
        return ! is_null($this->subscription($name));
    }

    /**
     * Determine if the model is actively subscribed to a given PayStack plan.
     *
     * The PayStack equivalent of Cashier's subscribedToPrice().
     */
    public function subscribedToPlan(string $plan): bool
    {
        return ! is_null($this->hasActivePlan($plan));
    }

    /**
     * Determine if the model is actively subscribed to any plan configured
     * under the given product (the `name` group in config/collector.php).
     *
     * The PayStack equivalent of Cashier's subscribedToProduct().
     */
    public function subscribedToProduct(string $product): bool
    {
        $planCodes = Collector::plans($this->collectorConfiguration('type'))
            ->where('name', $product)
            ->pluck('id')
            ->all();

        return $this->subscriptions
            ->whereIn('paystack_plan', $planCodes)
            ->where('paystack_status', Subscription::ACTIVE_STATUS)
            ->isNotEmpty();
    }

    /**
     * Pull every subscription PayStack holds for this customer.
     *
     * Walks the paginated endpoint so a customer with a long history is not
     * silently truncated to the first page.
     */
    public function payStackSubscriptions(): Collection
    {
        if (! $this->hasPayStackId()) {
            return collect();
        }

        $customerId = data_get($this->getAsPaystackCustomer(), 'id');

        if (! $customerId) {
            return collect();
        }

        $subscriptions = collect();
        $page = 1;

        do {
            $response = $this->request->get('/subscription', [
                'customer' => $customerId,
                'perPage' => 100,
                'page' => $page,
            ]);

            if (! $response->ok()) {
                break;
            }

            $subscriptions = $subscriptions->concat($response->json('data') ?? []);

            $pageCount = (int) data_get($response->json('meta'), 'pageCount', 1);
        } while ($page++ < $pageCount);

        return $subscriptions;
    }

    /**
     * Reconcile the local subscriptions table against PayStack.
     *
     * PayStack is the source of truth: a subscription started outside the
     * portal, or one whose local row was never written, would otherwise be
     * invisible — and invisible subscriptions cannot be cancelled when the
     * customer switches plans, which is how duplicate billing starts.
     */
    public function syncSubscriptions(): Collection
    {
        return $this->payStackSubscriptions()
            ->filter(fn($remote) => data_get($remote, 'subscription_code'))
            ->map(function ($remote) {
                $status = $this->mapPayStackStatus(data_get($remote, 'status'));

                return Subscription::$subscriptionModel::updateOrCreate(
                    ['paystack_id' => data_get($remote, 'subscription_code')],
                    [
                        'user_id' => $this->id,
                        'name' => data_get($remote, 'plan.name')
                            ?? data_get($remote, 'plan.plan_code')
                            ?? 'Subscription',
                        'quantity' => data_get($remote, 'quantity') ?? 1,
                        'paystack_email_token' => data_get($remote, 'email_token'),
                        'paystack_status' => $status,
                        'paystack_plan' => data_get($remote, 'plan.plan_code'),
                        'ends_at' => $status === Subscription::CANCELLED_STATUS
                            ? data_get($remote, 'next_payment_date')
                            : null,
                    ]
                );
            })
            ->values();
    }

    /**
     * Translate a PayStack subscription status into the package's vocabulary.
     *
     * PayStack's "non-renewing" means disabled but still valid until the period
     * ends — the package models that as cancelled with an ends_at grace period.
     */
    protected function mapPayStackStatus(?string $status): string
    {
        return match ($status) {
            'active' => Subscription::ACTIVE_STATUS,
            default => Subscription::CANCELLED_STATUS,
        };
    }

    /**
     * Generate a PayStack-hosted link for managing a subscription's card.
     *
     * PayStack has no API for replacing a stored card, so updating a payment
     * method is delegated to PayStack's own hosted page. The link is short
     * lived, so it is generated on demand rather than stored.
     */
    public function subscriptionManageLink(string $code): ?string
    {
        $response = $this->request->get("/subscription/$code/manage/link");

        if (! $response->ok()) {
            return null;
        }

        return data_get($response->json('data'), 'link');
    }

    /**
     * @return array|mixed|null
     */
    public function fetchSubscription(string $code)
    {
        $response = $this->request->get("/subscription/$code");

        if (! $response->ok()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * @return string|null
     */
    public function initiateTransaction($customer, $plan, array $options = [])
    {
        $response = $this->request->post('/transaction/initialize', array_merge([
            'email' => $customer->email,
            'plan' => $plan,
            'callback_url' => route('collector.portal'),
            'amount' => 1000, // plan option will override this amount :)
        ], $options));

        if (! $response->ok()) {
            return null;
        }

        return data_get($response->json('data'), 'authorization_url');
    }

    /**
     * @return array|null
     */
    public function completedTransaction(string $reference)
    {
        $response = $this->request->get("/transaction/verify/$reference");

        if (! $response->ok()) {
            return null;
        }

        if (data_get($response->json('data'), 'status') !== 'success') {
            return null;
        }

        return $response->json('data');
    }

    public function cancelOnPayStack($subscription)
    {
        $response = $this->request->post('subscription/disable', [
            'code' => $subscription->paystack_id,
            'token' => $subscription->paystack_email_token,
        ]);

        if (! $response->ok()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * @return HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::$subscriptionModel, $this->getForeignKey())
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return mixed
     */
    public function subscription($name = null)
    {
        if ($name) {
            return $this->subscriptions->where('name', $name)
                ->where('paystack_status', Subscription::ACTIVE_STATUS)
                ->first();
        }

        return $this->subscriptions->where('paystack_status', Subscription::ACTIVE_STATUS)->first();
    }
}
