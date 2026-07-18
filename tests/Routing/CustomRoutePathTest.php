<?php

use Collector\Tests\Factories\UserFactory;

it('serves the billing portal from the configured prefix and path', function () {
    expect(route('collector.portal', absolute: false))->toBe('/account/subscription-settings');

    fake_paystack();

    $this->actingAs(UserFactory::new()->withPaystackId()->create())
        ->get('/account/subscription-settings', inertia_headers())
        ->assertOk()
        ->assertJsonPath('component', 'Plans');
});

it('no longer serves the portal from the default path', function () {
    $this->actingAs(UserFactory::new()->create())
        ->get('/collector/billing')
        ->assertNotFound();
});

it('moves the subscription endpoints along with the prefix', function () {
    expect(route('collector.new-subscription', absolute: false))->toBe('/account/subscription')
        ->and(route('collector.cancel-subscription', absolute: false))->toBe('/account/subscription/cancel');
});

it('shares the relocated endpoints with the frontend', function () {
    fake_paystack();

    $this->actingAs(UserFactory::new()->withPaystackId()->create())
        ->get(route('collector.portal'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.collector.urls.subscribe', url('/account/subscription'))
        ->assertJsonPath('props.collector.urls.cancel', url('/account/subscription/cancel'))
        // Shipped pre-built, so the bundle cannot know the host app's name.
        ->assertJsonPath('props.collector.appName', config('app.name'));
});

it('leaves the webhook where paystack already knows to find it', function () {
    // The webhook path is pinned independently of the prefix, so an endpoint
    // already registered in the PayStack dashboard survives a portal move.
    expect(route('collector.webhook', absolute: false))->toBe('/collector/webhooks');

    // An event with no handler still exercises routing and signature verification.
    post_webhook(['event' => 'ping', 'data' => []])->assertOk();
});
