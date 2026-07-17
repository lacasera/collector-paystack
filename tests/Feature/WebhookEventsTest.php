<?php

use Collector\Events\InvoiceCreated;
use Collector\Events\PaymentReceived;
use Collector\Events\SubscriptionCanceled;
use Collector\Events\WebhookReceived;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Event;

it('always dispatches WebhookReceived for any signed event', function () {
    Event::fake([WebhookReceived::class]);

    post_webhook(['event' => 'anything.at_all', 'data' => []])->assertOk();

    Event::assertDispatched(WebhookReceived::class);
});

it('ignores an unknown event without error', function () {
    post_webhook(['event' => 'some.unhandled_event', 'data' => []])->assertOk();
});

it('dispatches InvoiceCreated on invoice.create', function () {
    Event::fake([InvoiceCreated::class]);
    UserFactory::new()->withPaystackId('CUS_test123')->create();

    post_webhook([
        'event' => 'invoice.create',
        'data' => ['customer' => ['customer_code' => 'CUS_test123']],
    ])->assertOk();

    Event::assertDispatched(InvoiceCreated::class);
});

it('dispatches PaymentReceived on invoice.payment_failed', function () {
    Event::fake([PaymentReceived::class]);
    UserFactory::new()->withPaystackId('CUS_test123')->create();

    post_webhook([
        'event' => 'invoice.payment_failed',
        'data' => ['customer' => ['customer_code' => 'CUS_test123']],
    ])->assertOk();

    Event::assertDispatched(PaymentReceived::class);
});

it('does nothing for an event whose customer is unknown', function () {
    Event::fake([PaymentReceived::class]);

    post_webhook([
        'event' => 'charge.success',
        'data' => ['customer' => ['customer_code' => 'CUS_unknown']],
    ])->assertOk();

    Event::assertNotDispatched(PaymentReceived::class);
});

it('dispatches SubscriptionCanceled on subscription.not_renew for an active plan', function () {
    Event::fake([SubscriptionCanceled::class]);

    $user = UserFactory::new()->withPaystackId('CUS_test123')->create();
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_id' => 'SUB_test123',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'paystack_status' => 'active',
    ]);

    post_webhook(paystack_fixture('webhooks/subscription-not-renew'))->assertOk();

    Event::assertDispatched(SubscriptionCanceled::class);
});
