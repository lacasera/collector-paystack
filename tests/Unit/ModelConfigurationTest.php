<?php

use Collector\Collector;
use Collector\CollectorManager;
use Collector\Models\Subscription;
use Collector\Tests\TestUser;

afterEach(function () {
    // Restore defaults mutated by these tests (global static state).
    CollectorManager::useCustomerModel(TestUser::class);
    CollectorManager::useSubscriptionModel(Subscription::class);
});

it('configures both the collectable and the subscription owner model with one call', function () {
    CollectorManager::useCustomerModel('App\\Models\\CustomUser');

    expect(Subscription::$customerModel)->toBe('App\\Models\\CustomUser');
});

it('configures the customer model through the Collector facade', function () {
    Collector::useCustomerModel('App\\Models\\FacadeUser');

    expect(Subscription::$customerModel)->toBe('App\\Models\\FacadeUser');
});

it('configures the subscription model', function () {
    CollectorManager::useSubscriptionModel('App\\Models\\CustomSubscription');

    expect(Subscription::$subscriptionModel)->toBe('App\\Models\\CustomSubscription');
});
