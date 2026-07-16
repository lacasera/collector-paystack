<?php

use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;

it('creates a subscription from subscription.create for a new customer', function () {
    fake_paystack();

    $user = UserFactory::new()->create(['email' => 'customer@example.com']);

    post_webhook(paystack_fixture('webhooks/subscription-create'))->assertOk();

    // The customer code from the payload is persisted...
    expect($user->fresh()->paystack_id)->toBe('CUS_test123');

    // ...and the matching PayStack subscription is stored locally.
    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'paystack_id' => 'SUB_test123',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);
});

it('handles subscription.create for an existing customer without erroring', function () {
    // Regression: previously the customer was only fetched when the user had NO
    // paystack id, leaving $paystackCustomer undefined for returning customers
    // and throwing a TypeError (500). It must now succeed.
    fake_paystack();

    $user = UserFactory::new()->withPaystackId('CUS_test123')->create([
        'email' => 'customer@example.com',
    ]);

    post_webhook(paystack_fixture('webhooks/subscription-create'))->assertOk();

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'paystack_id' => 'SUB_test123',
    ]);
});

it('does not duplicate a subscription the customer already has active', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId('CUS_test123')->create([
        'email' => 'customer@example.com',
    ]);

    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);

    post_webhook(paystack_fixture('webhooks/subscription-create'))->assertOk();

    expect(Subscription::where('user_id', $user->id)->count())->toBe(1);
});
