<?php

use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Subscription helpers
|--------------------------------------------------------------------------
*/

it('reports whether the model is subscribed', function () {
    $user = UserFactory::new()->create();

    expect($user->subscribed())->toBeFalse();

    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_status' => 'active',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
    ]);

    expect($user->fresh()->subscribed())->toBeTrue();
});

it('reports subscription to a specific plan (subscribedToPlan)', function () {
    $user = UserFactory::new()->create();
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_status' => 'active',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
    ]);

    $user = $user->fresh();

    expect($user->subscribedToPlan('PLN_worid7k3e8v5afz'))->toBeTrue()
        ->and($user->subscribedToPlan('PLN_does_not_exist'))->toBeFalse();
});

it('reports subscription to a product (config plan-name group)', function () {
    $user = UserFactory::new()->create();
    // PLN_worid7k3e8v5afz is a monthly plan under the "Basic" product in config.
    SubscriptionFactory::new()->forUser($user)->create([
        'paystack_status' => 'active',
        'paystack_plan' => 'PLN_worid7k3e8v5afz',
        'name' => 'Basic',
    ]);

    $user = $user->fresh();

    expect($user->subscribedToProduct('Basic'))->toBeTrue()
        ->and($user->subscribedToProduct('Premium'))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Customer helpers
|--------------------------------------------------------------------------
*/

it('creates a paystack customer (createAsPayStackCustomer)', function () {
    fake_paystack();

    $user = UserFactory::new()->create();
    $user->createAsPayStackCustomer(['email' => $user->email]);

    expect($user->paystack_id)->toBe('CUS_test123')
        ->and($user->pm_last_four)->toBe('4081');
});

it('updates a paystack customer (updatePayStackCustomer)', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();
    $result = $user->updatePayStackCustomer(['first_name' => 'Ada']);

    expect($result)->not->toBeNull();
    Http::assertSent(fn($request) => $request->method() === 'PUT'
        && str_contains($request->url(), 'customer/CUS_test123'));
});

it('throws when updating a customer that does not exist on paystack', function () {
    UserFactory::new()->create()->updatePayStackCustomer(['first_name' => 'Ada']);
})->throws(Exception::class);

/*
|--------------------------------------------------------------------------
| Payment methods
|--------------------------------------------------------------------------
*/

it('lists payment methods from paystack authorizations', function () {
    fake_paystack();

    $methods = UserFactory::new()->withPaystackId()->create()->paymentMethods();

    expect($methods)->toHaveCount(1)
        ->and($methods->first()['last4'])->toBe('4081');
});

it('returns an empty collection of payment methods for a non-customer', function () {
    expect(UserFactory::new()->create()->paymentMethods())->toBeEmpty();
});

it('exposes the stored default payment method', function () {
    $user = UserFactory::new()->create([
        'pm_type' => 'visa',
        'pm_last_four' => '4081',
        'pm_expiration' => '12/2030',
    ]);

    expect($user->hasPaymentMethod())->toBeTrue()
        ->and($user->defaultPaymentMethod()->last4)->toBe('4081');
});

it('reports no payment method when none is stored', function () {
    $user = UserFactory::new()->create();

    expect($user->hasPaymentMethod())->toBeFalse()
        ->and($user->defaultPaymentMethod())->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Billing portal
|--------------------------------------------------------------------------
*/

it('returns the billing portal url', function () {
    expect(UserFactory::new()->create()->billingPortalUrl())->toBe(route('collector.portal'));
});
