<?php

use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

function subscribed_user()
{
    $user = UserFactory::new()->withPaystackId()->create();

    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_id' => 'SUB_test123',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);

    return $user;
}

it('requires authentication', function () {
    $this->getJson(route('collector.manage'))->assertUnauthorized();
});

it('renders the management portal with the current plan', function () {
    fake_paystack();

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('component', 'Manage')
        ->assertJsonPath('props.currentPlan.planCode', 'PLN_worid7k3e8v5afz')
        ->assertJsonPath('props.currentPlan.name', 'Basic');
});

it('exposes payment history from paystack', function () {
    fake_paystack();

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.transactions.data.0.reference', 'REF_test123')
        ->assertJsonPath('props.transactions.data.0.status', 'success')
        // PayStack returns card_type with a trailing space; it must not leak.
        ->assertJsonPath('props.transactions.data.0.brand', 'visa');
});

it('exposes saved payment methods and subscription history', function () {
    fake_paystack();

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.paymentMethods.0.last4', '4081')
        ->assertJsonPath('props.subscriptions.0.code', 'SUB_test123')
        ->assertJsonPath('props.subscriptions.0.status', Subscription::ACTIVE_STATUS);
});

it('collapses repeat authorizations for the same card', function () {
    $card = ['card_type' => 'visa ', 'last4' => '4081', 'exp_month' => '12', 'exp_year' => '2030', 'signature' => 'SIG_same'];

    // PayStack stores one authorization per transaction, so a customer who has
    // paid five times with one card has five identical authorizations.
    fake_paystack([
        'https://api.paystack.co/customer*' => Http::response([
            'status' => true,
            'data' => array_merge(paystack_fixture('customer'), [
                'authorizations' => [$card, $card, $card, array_merge($card, [
                    'last4' => '1234',
                    'signature' => 'SIG_other',
                ])],
            ]),
        ]),
    ]);

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonCount(2, 'props.paymentMethods')
        ->assertJsonPath('props.paymentMethods.0.last4', '4081')
        ->assertJsonPath('props.paymentMethods.1.last4', '1234');
});

it('fetches the paystack customer once per page load', function () {
    fake_paystack();

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk();

    // Subscription sync, transaction history and payment methods each need the
    // customer; without memoisation that is three identical blocking requests.
    $customerRequests = collect(Http::recorded())
        ->filter(fn($pair) => str_contains($pair[0]->url(), '/customer/'))
        ->count();

    expect($customerRequests)->toBe(1);
});

it('reports the card bound to the subscription, not merely a stored one', function () {
    // The customer has paid with two cards, so two authorizations are stored.
    // Only the one attached to the subscription will be charged at renewal.
    fake_paystack([
        'https://api.paystack.co/customer*' => Http::response([
            'status' => true,
            'data' => array_merge(paystack_fixture('customer'), [
                'authorizations' => [
                    ['card_type' => 'mastercard', 'last4' => '9999', 'signature' => 'SIG_other'],
                    ['card_type' => 'visa ', 'last4' => '4081', 'signature' => 'SIG_sub'],
                ],
            ]),
        ]),
        // Overrides the existing key rather than adding a narrower one: fake()
        // appends stubs and the broader 'subscription/*' would win on order.
        'https://api.paystack.co/subscription/*' => Http::response([
            'status' => true,
            'data' => array_merge(paystack_fixture('subscription'), [
                'authorization' => ['card_type' => 'visa ', 'last4' => '4081'],
            ]),
        ]),
    ]);

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.currentPlan.card.last4', '4081')
        // Trailing space from PayStack must not leak into the UI.
        ->assertJsonPath('props.currentPlan.card.brand', 'visa');
});

it('opens on the overview by default', function () {
    fake_paystack();

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.section', 'overview');
});

it('opens on the section named in the url', function () {
    fake_paystack();

    // Lets an application deep-link, e.g. after a failed payment:
    // route('collector.manage', ['section' => 'methods'])
    $this->actingAs(subscribed_user())
        ->get(route('collector.manage', ['section' => 'methods']), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.section', 'methods');
});

it('falls back to the overview for an unknown section', function () {
    fake_paystack();

    // A stale bookmark or a typo should still land somewhere useful.
    $this->actingAs(subscribed_user())
        ->get(route('collector.manage', ['section' => 'nonsense']), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.section', 'overview');
});

it('keeps the requested section while paging through payment history', function () {
    fake_paystack();

    $this->actingAs(subscribed_user())
        ->get(route('collector.manage', ['section' => 'history', 'page' => 2]), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.section', 'history');

    // The page must actually reach PayStack, not just survive in the URL.
    Http::assertSent(fn($request) => str_contains($request->url(), '/transaction')
        && str_contains($request->url(), 'page=2'));
});

it('keeps showing a cancelled subscription while it is in its grace period', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_id' => 'SUB_test123',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => Subscription::CANCELLED_STATUS,
        'ends_at' => now()->addDays(5),
    ]);

    // Cancelling must not make the subscription vanish — the customer still
    // has access and needs to see until when.
    $this->actingAs($user)
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.currentPlan.planCode', 'PLN_worid7k3e8v5afz')
        ->assertJsonPath('props.currentPlan.onGracePeriod', true);
});

it('prefers the active subscription over an older one still in its grace period', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();

    // Active, but created first.
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_id' => 'SUB_test123',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => Subscription::ACTIVE_STATUS,
    ]);

    // Cancelled but not yet expired, and newer — must not shadow the plan the
    // customer is actually paying for.
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_id' => 'SUB_stale',
        'paystack_plan' => 'PLN_wc54sx7clavvy6d',
        'paystack_status' => Subscription::CANCELLED_STATUS,
        'ends_at' => now()->addDays(30),
    ]);

    $this->actingAs($user)
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.currentPlan.planCode', 'PLN_worid7k3e8v5afz')
        ->assertJsonPath('props.currentPlan.onGracePeriod', false);
});

it('reports an unknown amount rather than zero when paystack cannot be reached', function () {
    fake_paystack([
        // Must override the existing key: a narrower one is appended after
        // 'subscription/*' and would never be reached.
        'https://api.paystack.co/subscription/*' => Http::response(['status' => false], 500),
    ]);

    // "GHS 0" would read as free; null lets the portal show a dash instead.
    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.currentPlan.amount', null);
});

it('reports no current plan when nothing is active', function () {
    fake_paystack();

    $this->actingAs(UserFactory::new()->withPaystackId()->create())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('props.currentPlan', null);
});

it('is not shadowed by the portal catch-all route', function () {
    fake_paystack();

    // /manage must reach the management controller rather than being read as a
    // collectable type by the portal's optional {type?} segment.
    $this->actingAs(subscribed_user())
        ->get(route('collector.manage'), inertia_headers())
        ->assertOk()
        ->assertJsonPath('component', 'Manage');
});

it('mints a paystack hosted link for updating the card', function () {
    fake_paystack();

    $this->actingAs(subscribed_user())
        ->postJson(route('collector.update-payment-method'))
        ->assertOk()
        ->assertJsonPath('redirect', 'https://paystack.com/manage/subscriptions/test123?subscription_token=tok');
});

it('refuses a card update without an active subscription', function () {
    fake_paystack();

    $this->actingAs(UserFactory::new()->withPaystackId()->create())
        ->postJson(route('collector.update-payment-method'))
        ->assertStatus(422);
});

it('surfaces a paystack failure when the link cannot be generated', function () {
    fake_paystack([
        'https://api.paystack.co/subscription/*/manage/link' => Http::response(['status' => false], 500),
    ]);

    $this->actingAs(subscribed_user())
        ->postJson(route('collector.update-payment-method'))
        ->assertStatus(502);
});
