<?php

use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;

it('scopes subscriptions with an expired trial', function () {
    SubscriptionFactory::new()->create(['trial_ends_at' => now()->subDay()]);
    SubscriptionFactory::new()->create(['trial_ends_at' => now()->addDay()]);
    SubscriptionFactory::new()->create(['trial_ends_at' => null]);

    expect(Subscription::query()->expiredTrial()->count())->toBe(1);
});

it('scopes subscriptions not on trial (null or past)', function () {
    SubscriptionFactory::new()->create(['trial_ends_at' => now()->addDay()]);
    SubscriptionFactory::new()->create(['trial_ends_at' => now()->subDay()]);
    SubscriptionFactory::new()->create(['trial_ends_at' => null]);

    expect(Subscription::query()->notOnTrial()->count())->toBe(2);
});

it('scopes subscriptions on and off their grace period', function () {
    SubscriptionFactory::new()->create(['ends_at' => now()->addDays(5)]);
    SubscriptionFactory::new()->create(['ends_at' => now()->subDay()]);
    SubscriptionFactory::new()->create(['ends_at' => null]);

    expect(Subscription::query()->onGracePeriod()->count())->toBe(1)
        ->and(Subscription::query()->notOnGracePeriod()->count())->toBe(2);
});

it('exposes trial state helpers', function () {
    $onTrial = SubscriptionFactory::new()->create(['trial_ends_at' => now()->addDay()]);
    $expired = SubscriptionFactory::new()->create(['trial_ends_at' => now()->subDay()]);

    expect($onTrial->onTrial())->toBeTrue()
        ->and($onTrial->hasExpiredTrial())->toBeFalse()
        ->and($expired->onTrial())->toBeFalse()
        ->and($expired->hasExpiredTrial())->toBeTrue();
});
