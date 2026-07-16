<?php

use Collector\Tests\Factories\SubscriptionFactory;

it('reports an active subscription as active (based on paystack_status)', function () {
    // Regression: isActive() previously read a non-existent `status` column, so
    // an active subscription reported false. It must read `paystack_status`.
    $subscription = SubscriptionFactory::new()->create();

    expect($subscription->isActive())->toBeTrue()
        ->and($subscription->valid())->toBeTrue();
});

it('reports a cancelled, expired subscription as inactive', function () {
    $subscription = SubscriptionFactory::new()->cancelled()->create();

    expect($subscription->isActive())->toBeFalse()
        ->and($subscription->valid())->toBeFalse();
});

it('treats a cancelled subscription still within its grace period as valid', function () {
    $subscription = SubscriptionFactory::new()->onGracePeriod()->create();

    expect($subscription->onGracePeriod())->toBeTrue()
        ->and($subscription->isActive())->toBeTrue()
        ->and($subscription->valid())->toBeTrue();
});
