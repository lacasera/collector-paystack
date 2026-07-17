<?php

use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

it('creates a subscription and returns the paystack checkout redirect', function () {
    fake_paystack();

    $user = UserFactory::new()->create();

    $this->actingAs($user)
        ->postJson(route('collector.new-subscription'), ['plan' => 'PLN_worid7k3e8v5afz'])
        ->assertCreated()
        ->assertJson(['redirect' => 'https://checkout.paystack.test/redirect/REF_test123']);

    // The customer was registered on PayStack during checkout.
    expect($user->fresh()->paystack_id)->toBe('CUS_test123');
    Http::assertSent(fn($request) => str_contains($request->url(), '/transaction/initialize'));
});

it('rejects an unknown plan with a validation error (ValidationRule)', function () {
    fake_paystack();

    $this->actingAs(UserFactory::new()->create())
        ->postJson(route('collector.new-subscription'), ['plan' => 'PLN_does_not_exist'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

it('requires a plan', function () {
    $this->actingAs(UserFactory::new()->create())
        ->postJson(route('collector.new-subscription'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

it('cancels the existing active subscription when switching plans', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();

    $old = SubscriptionFactory::new()->forUser($user)->create([
        'paystack_plan' => 'PLN_wc54sx7clavvy6d',
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);

    $this->actingAs($user)
        ->postJson(route('collector.new-subscription'), ['plan' => 'PLN_worid7k3e8v5afz'])
        ->assertCreated();

    expect($old->fresh()->paystack_status)->toBe(Subscription::CANCELLED_STATUS);
    Http::assertSent(fn($request) => str_contains($request->url(), '/subscription/disable'));
});
