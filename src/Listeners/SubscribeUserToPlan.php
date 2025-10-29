<?php

namespace Collector\Listeners;

use Collector\Events\PaymentVerified;
use Collector\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SubscribeUserToPlan
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(PaymentVerified $event): void
    {
        $collectable = $event->collectable;

        $transaction = $event->collectable->completedTransaction($event->reference);

        if (data_get($transaction, 'status') === 'success') {
            $collectable->fill([
                'pm_type' => data_get($transaction, 'authorization.card_type'),
                'pm_last_four' => data_get($transaction, 'authorization.last4'),
                'pm_expiration' => data_get($transaction, 'authorization.exp_month') . '/' . data_get($transaction, 'authorization.exp_year'),
            ])->save();
        }

        $planCode = data_get($transaction, 'plan_object.plan_code');

        if (! $collectable->hasActivePlan($planCode)) {
            $paystackSubscription = $collectable->getAsPaystackCustomer();

            $paystackSubscription = $this->guessSubscription(
                $collectable,
                data_get($paystackSubscription, 'subscriptions'),
                $planCode
            );

            if ($paystackSubscription) {

                /** @var Model $model */
                $model = new Subscription::$subscriptionModel();

                $model->fill([
                    'name' => data_get($paystackSubscription, 'plan.name'),
                    'user_id' => $collectable->id,
                    'quantity' => 1,
                    'paystack_email_token' => data_get($paystackSubscription, 'email_token'),
                    'paystack_id' => data_get($paystackSubscription, 'subscription_code'),
                    'paystack_status' => data_get($paystackSubscription, 'status'),
                    'paystack_plan' => $planCode,
                ])->save();
            }
        }
    }

    private function guessSubscription($collectable, array $subscriptions, $plan)
    {
        $ids = Arr::pluck($subscriptions, 'subscription_code');

        foreach ($ids as $id) {
            $subscription = $collectable->fetchSubscription($id);

            if ($subscription && data_get($subscription, 'plan.plan_code') === $plan) {
                return $subscription;
            }
        }

        return null;
    }
}
