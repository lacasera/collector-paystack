<?php

use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

/**
 * Fake the paginated subscription-list endpoint PayStack exposes for a customer.
 */
function fake_paystack_subscriptions(array $subscriptions): void
{
    fake_paystack([
        'https://api.paystack.co/subscription?*' => Http::response([
            'status' => true,
            'data' => $subscriptions,
            'meta' => ['total' => count($subscriptions), 'perPage' => 100, 'page' => 1, 'pageCount' => 1],
        ]),
    ]);
}

function remote_subscription(string $code, string $status, string $planCode): array
{
    return [
        'subscription_code' => $code,
        'email_token' => 'tok_' . $code,
        'status' => $status,
        'quantity' => 1,
        'next_payment_date' => '2026-09-01T00:00:00.000Z',
        'plan' => ['plan_code' => $planCode, 'name' => 'Basic'],
    ];
}

it('imports subscriptions that exist only on paystack', function () {
    fake_paystack_subscriptions([
        remote_subscription('SUB_remote1', 'active', 'PLN_worid7k3e8v5afz'),
        remote_subscription('SUB_remote2', 'non-renewing', 'PLN_wc54sx7clavvy6d'),
    ]);

    $user = UserFactory::new()->withPaystackId()->create();

    expect(Subscription::count())->toBe(0);

    $user->syncSubscriptions();

    expect(Subscription::count())->toBe(2)
        ->and(Subscription::where('paystack_id', 'SUB_remote1')->first()->paystack_status)
        ->toBe(Subscription::ACTIVE_STATUS)
        // non-renewing is disabled-but-running, which the package models as
        // cancelled with a grace period rather than as active.
        ->and(Subscription::where('paystack_id', 'SUB_remote2')->first()->paystack_status)
        ->toBe(Subscription::CANCELLED_STATUS);
});

it('clears the grace period when a subscription goes back to active', function () {
    // One stub whose answer changes, rather than two Http::fake() calls:
    // fake() appends stubs and the first match wins, so a second registration
    // for the same URL would never be reached.
    $status = 'non-renewing';

    fake_paystack([
        'https://api.paystack.co/subscription?*' => function () use (&$status) {
            return Http::response([
                'status' => true,
                'data' => [remote_subscription('SUB_remote1', $status, 'PLN_worid7k3e8v5afz')],
                'meta' => ['total' => 1, 'perPage' => 100, 'page' => 1, 'pageCount' => 1],
            ]);
        },
    ]);

    $user = UserFactory::new()->withPaystackId()->create();

    $user->syncSubscriptions();

    expect(Subscription::where('paystack_id', 'SUB_remote1')->first()->ends_at)->not->toBeNull();

    // Re-enabled on the PayStack dashboard. A stale ends_at would keep the
    // subscription reporting itself as cancelled, hiding the cancel action
    // from a customer who is still being billed.
    $status = 'active';

    $user->syncSubscriptions();

    $subscription = Subscription::where('paystack_id', 'SUB_remote1')->first();

    expect($subscription->paystack_status)->toBe(Subscription::ACTIVE_STATUS)
        ->and($subscription->ends_at)->toBeNull()
        ->and($subscription->onGracePeriod())->toBeFalse();
});

it('survives a subscription whose plan carries no name', function () {
    // subscriptions.name is not nullable, so a missing plan name would
    // otherwise abort the sync — and with it the whole portal.
    $remote = remote_subscription('SUB_nameless', 'active', 'PLN_worid7k3e8v5afz');
    unset($remote['plan']['name']);

    fake_paystack_subscriptions([$remote]);

    $user = UserFactory::new()->withPaystackId()->create();

    $user->syncSubscriptions();

    expect(Subscription::where('paystack_id', 'SUB_nameless')->first()->name)
        ->toBe('PLN_worid7k3e8v5afz');
});

it('does not duplicate rows when synced twice', function () {
    fake_paystack_subscriptions([
        remote_subscription('SUB_remote1', 'active', 'PLN_worid7k3e8v5afz'),
    ]);

    $user = UserFactory::new()->withPaystackId()->create();

    $user->syncSubscriptions();
    $user->syncSubscriptions();

    expect(Subscription::count())->toBe(1);
});

it('cancels a paystack subscription that was missing locally before checking out', function () {
    // The double-billing scenario: PayStack is already billing this plan, but
    // no local row exists, so the usual cancel-then-switch would miss it.
    fake_paystack_subscriptions([
        remote_subscription('SUB_orphan', 'active', 'PLN_worid7k3e8v5afz'),
    ]);

    $user = UserFactory::new()->withPaystackId()->create();

    $user->newSubscription('default', 'PLN_wc54sx7clavvy6d')->checkout();

    $orphan = Subscription::where('paystack_id', 'SUB_orphan')->first();

    expect($orphan)->not->toBeNull()
        ->and($orphan->paystack_status)->toBe(Subscription::CANCELLED_STATUS);

    Http::assertSent(fn($request) => str_contains($request->url(), 'subscription/disable')
        && data_get($request->data(), 'code') === 'SUB_orphan');
});

it('resolves the current active plan deterministically', function () {
    $user = UserFactory::new()->withPaystackId()->create();

    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_id' => 'SUB_older',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);

    $newest = SubscriptionFactory::new()->forUser($user)->create([
        'paystack_id' => 'SUB_newer',
        'paystack_plan' => 'PLN_wc54sx7clavvy6d',
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);

    expect($user->currentActivePlan()->paystack_id)->toBe($newest->paystack_id);
});
