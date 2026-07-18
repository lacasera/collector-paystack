<?php

use Collector\Collector;
use Collector\Events\PaymentVerified;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

function subscribed_collectable()
{
    $user = UserFactory::new()->withPaystackId()->create();

    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => 'active',
    ]);

    return $user;
}

it('renders the plans page for a collectable with no subscription', function () {
    fake_paystack();

    // Assert the Inertia XHR JSON payload directly (component + props).
    $this->actingAs(UserFactory::new()->withPaystackId()->create())
        ->get(route('collector.portal'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('component', 'Plans')
        ->assertJsonPath('props.subscribed', null);
});

it('forwards a subscriber to the management portal', function () {
    fake_paystack();

    $this->actingAs(subscribed_collectable())
        ->get(route('collector.portal'))
        ->assertRedirect(route('collector.manage'));
});

it('lets a subscriber through to the plans page to switch plans', function () {
    fake_paystack();

    // Without this escape hatch the "Change plan" action would bounce straight
    // back to the management portal and appear to do nothing.
    $this->actingAs(subscribed_collectable())
        ->get(route('collector.portal', ['change' => 1]), inertia_headers())
        ->assertOk()
        ->assertJsonPath('component', 'Plans')
        ->assertJsonPath('props.subscribed', 'PLN_worid7k3e8v5afz');
});

it('still shows the plans page during the cancellation grace period', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => 'cancelled',
        'ends_at' => now()->addDays(5),
    ]);

    // A cancelled subscription is not "active", so the customer lands on the
    // plans page — resubscribing is the likely next step.
    $this->actingAs($user)
        ->get(route('collector.portal'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('component', 'Plans');
});

it('dispatches PaymentVerified then redirects to a reference-free url', function () {
    Event::fake([PaymentVerified::class]);

    $user = UserFactory::new()->withPaystackId()->create();

    // A reference is handled once and then stripped, so reloading the callback
    // URL cannot re-run payment verification.
    $this->actingAs($user)
        ->get(route('collector.portal', ['reference' => 'REF_test123']))
        ->assertRedirect(route('collector.portal'));

    Event::assertDispatched(PaymentVerified::class, fn($event) => $event->reference === 'REF_test123');
});

it('flashes success once a payment produces an active subscription', function () {
    fake_paystack();

    // The message the portal shows must reflect what actually happened, so it
    // is decided after the verification listener has run.
    $this->actingAs(subscribed_collectable())
        ->get(route('collector.portal', ['reference' => 'REF_test123']))
        ->assertRedirect(route('collector.portal'))
        ->assertSessionHas('collector.flash.success');
});

it('flashes an error when a payment leaves no subscription behind', function () {
    // PayStack reports the transaction as failed, so no subscription is
    // recorded and the customer must not be told they are subscribed.
    fake_paystack([
        'https://api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'failed'],
        ]),
    ]);

    $this->actingAs(UserFactory::new()->withPaystackId()->create())
        ->get(route('collector.portal', ['reference' => 'REF_unverified']))
        ->assertRedirect(route('collector.portal'))
        ->assertSessionHas('collector.flash.error');
});

it('verifies payment before forwarding an existing subscriber', function () {
    Event::fake([PaymentVerified::class]);
    fake_paystack();

    // Ordering guard: if the subscriber redirect ran first, renewals and plan
    // switches would stop being verified entirely.
    $this->actingAs(subscribed_collectable())
        ->get(route('collector.portal', ['reference' => 'REF_test123']))
        ->assertRedirect(route('collector.portal'));

    Event::assertDispatched(PaymentVerified::class);
});

it('requires authentication', function () {
    $this->getJson(route('collector.portal'))->assertUnauthorized();
});

it('returns 404 for a collectable type with no configured model', function () {
    $this->actingAs(UserFactory::new()->create())
        ->get(route('collector.portal', ['type' => 'nonexistent', 'id' => 1]), inertia_headers())
        ->assertNotFound();
});

it('returns 403 when the authorization callback denies access', function () {
    fake_paystack();

    Collector::authorizeUsing('user', fn() => false);

    $this->actingAs(UserFactory::new()->create())
        ->get(route('collector.portal'), inertia_headers())
        ->assertForbidden();
});
