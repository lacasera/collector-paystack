<?php

use Collector\Collector;
use Collector\Events\PaymentVerified;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Event;

it('renders the plans page with the current subscribed plan', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => 'active',
    ]);

    // Assert the Inertia XHR JSON payload directly (component + props).
    $this->actingAs($user)
        ->get(route('collector.portal'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('component', 'Plans')
        ->assertJsonPath('props.subscribed', 'PLN_worid7k3e8v5afz');
});

it('dispatches PaymentVerified when a payment reference is present', function () {
    fake_paystack();
    Event::fake([PaymentVerified::class]);

    $user = UserFactory::new()->withPaystackId()->create();

    $this->actingAs($user)
        ->get(route('collector.portal', ['reference' => 'REF_test123']), inertia_headers())
        ->assertOk();

    Event::assertDispatched(PaymentVerified::class, fn($event) => $event->reference === 'REF_test123');
});

it('requires authentication', function () {
    $this->getJson(route('collector.portal'))->assertUnauthorized();
});

it('returns 404 for a collectable type with no configured model', function () {
    $this->actingAs(UserFactory::new()->create())
        ->get('/collector/billing/nonexistent/1', inertia_headers())
        ->assertNotFound();
});

it('returns 403 when the authorization callback denies access', function () {
    fake_paystack();

    Collector::authorizeUsing('user', fn() => false);

    $this->actingAs(UserFactory::new()->create())
        ->get(route('collector.portal'), inertia_headers())
        ->assertForbidden();
});
