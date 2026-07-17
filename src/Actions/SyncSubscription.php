<?php

namespace Collector\Actions;

use Collector\Models\Subscription;
use Illuminate\Support\Arr;

class SyncSubscription
{
    public function sync($collectable, ?string $planCode): ?Subscription
    {
        if (! $planCode || ! $collectable->hasPayStackId()) {
            return null;
        }

        if ($existing = $collectable->hasActivePlan($planCode)) {
            return $existing;
        }

        $paystackCustomer = $collectable->getAsPaystackCustomer();

        $paystackSubscription = $this->guess(
            $collectable,
            data_get($paystackCustomer, 'subscriptions', []),
            $planCode
        );

        if (! $paystackSubscription) {
            return null;
        }

        return Subscription::$subscriptionModel::updateOrCreate(
            ['paystack_id' => data_get($paystackSubscription, 'subscription_code')],
            [
                'name' => data_get($paystackSubscription, 'plan.name'),
                'user_id' => $collectable->id,
                'quantity' => 1,
                'paystack_email_token' => data_get($paystackSubscription, 'email_token'),
                'paystack_status' => data_get($paystackSubscription, 'status'),
                'paystack_plan' => $planCode,
            ]
        );
    }

    /**
     * Find the collectable's PayStack subscription whose plan matches $planCode.
     */
    private function guess($collectable, array $subscriptions, string $planCode): ?array
    {
        foreach (Arr::pluck($subscriptions, 'subscription_code') as $id) {
            $subscription = $collectable->fetchSubscription($id);

            if ($subscription && data_get($subscription, 'plan.plan_code') === $planCode) {
                return $subscription;
            }
        }

        return null;
    }
}
