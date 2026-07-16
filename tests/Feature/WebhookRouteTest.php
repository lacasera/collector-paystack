<?php

use Collector\Events\PaymentReceived;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Event;

it('rejects a webhook with an invalid signature (403)', function () {
    Event::fake([PaymentReceived::class]);

    $response = post_webhook(paystack_fixture('webhooks/charge-success'), signature: 'not-a-valid-signature');

    $response->assertForbidden();
    Event::assertNotDispatched(PaymentReceived::class);
});

it('accepts a correctly signed charge.success and dispatches PaymentReceived', function () {
    Event::fake([PaymentReceived::class]);

    UserFactory::new()->withPaystackId('CUS_test123')->create();

    $response = post_webhook(paystack_fixture('webhooks/charge-success'));

    $response->assertOk();
    Event::assertDispatched(PaymentReceived::class);
});
