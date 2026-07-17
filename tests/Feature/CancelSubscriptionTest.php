<?php

use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

it('cancels the active subscription and records the reason', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();
    $subscription = SubscriptionFactory::new()->forUser($user)->create([
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);

    $this->actingAs($user)
        ->postJson(route('collector.cancel-subscription'), ['reason' => 'too expensive'])
        ->assertOk()
        ->assertJson(['data' => 'Your subscription has been successfully cancelled.']);

    $subscription->refresh();

    expect($subscription->paystack_status)->toBe(Subscription::CANCELLED_STATUS)
        ->and($subscription->cancelation_reason)->toBe('too expensive');

    Http::assertSent(fn($request) => str_contains($request->url(), '/subscription/disable'));
});

it('returns a validation error when there is no active subscription', function () {
    $this->actingAs(UserFactory::new()->create())
        ->postJson(route('collector.cancel-subscription'))
        ->assertStatus(422);
});
