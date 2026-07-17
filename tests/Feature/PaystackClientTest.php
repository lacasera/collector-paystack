<?php

use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

it('does not call paystack when the customer already exists', function () {
    Http::fake();

    $user = UserFactory::new()->withPaystackId()->create();
    $user->createOrGetPayStackCustomer(['email' => $user->email]);

    Http::assertNothingSent();
});

it('stores the customer code and card details when creating a customer', function () {
    fake_paystack();

    $user = UserFactory::new()->create();
    $user->createOrGetPayStackCustomer(['email' => $user->email]);

    expect($user->paystack_id)->toBe('CUS_test123')
        ->and($user->pm_last_four)->toBe('4081');
});

it('throws when fetching the paystack customer for a user without an id', function () {
    UserFactory::new()->create()->getAsPaystackCustomer();
})->throws(Exception::class);

it('returns null when the paystack customer lookup fails', function () {
    Http::fake(['https://api.paystack.co/customer/*' => Http::response([], 500)]);

    expect(UserFactory::new()->withPaystackId()->create()->getAsPaystackCustomer())->toBeNull();
});

it('returns null when a subscription fetch fails', function () {
    Http::fake(['https://api.paystack.co/subscription/*' => Http::response([], 500)]);

    expect(UserFactory::new()->create()->fetchSubscription('SUB_x'))->toBeNull();
});

it('returns null when transaction initialization fails', function () {
    Http::fake(['https://api.paystack.co/transaction/initialize*' => Http::response([], 500)]);

    $user = UserFactory::new()->create();

    expect($user->initiateTransaction($user, 'PLN_x'))->toBeNull();
});
