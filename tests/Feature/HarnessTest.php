<?php

use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Collector\Tests\TestUser;
use Illuminate\Support\Facades\Http;

it('boots the app and migrates the users + subscriptions tables', function () {
    expect(Schema::hasTable('users'))->toBeTrue();
    expect(Schema::hasTable('subscriptions'))->toBeTrue();
    expect(Schema::hasColumn('users', 'paystack_id'))->toBeTrue();
});

it('creates a user via the factory', function () {
    $user = UserFactory::new()->create();

    expect($user)->toBeInstanceOf(TestUser::class)
        ->and($user->exists)->toBeTrue()
        ->and($user->email)->not->toBeEmpty();
});

it('creates a subscription tied to a user', function () {
    $user = UserFactory::new()->withPaystackId()->create();

    $subscription = SubscriptionFactory::new()->forUser($user)->create();

    expect($subscription->paystack_status)->toBe(Subscription::ACTIVE_STATUS)
        ->and($subscription->user->is($user))->toBeTrue();
});

it('loads Paystack fixtures', function () {
    $customer = paystack_fixture('customer');

    expect($customer)->toHaveKey('customer_code', 'CUS_test123');
    expect(paystack_fixture('plans'))->toBeArray()->not->toBeEmpty();
});

it('fakes the Paystack API for outbound calls', function () {
    fake_paystack();

    $response = Http::asJson()->get('https://api.paystack.co/plan');

    expect($response->json('data'))->toBeArray()->not->toBeEmpty();
    Http::assertSent(fn($request) => str_contains($request->url(), 'api.paystack.co/plan'));
});

it('produces a valid webhook signature that the package can verify', function () {
    $body = json_encode(paystack_fixture('webhooks/charge-success'));

    $headers = paystack_webhook_headers($body);

    $expected = hash_hmac('sha512', $body, config('collector.secret'));

    expect($headers['X-Paystack-Signature'])->toBe($expected);
});
