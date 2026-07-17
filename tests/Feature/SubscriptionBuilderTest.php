<?php

use Collector\Checkout;
use Collector\Events\PaymentVerified;
use Collector\Models\Subscription;
use Collector\Tests\Factories\SubscriptionFactory;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

it('starts a checkout and returns the paystack authorization url', function () {
    fake_paystack();

    $checkout = UserFactory::new()->create()
        ->newSubscription('default', 'PLN_worid7k3e8v5afz')
        ->checkout();

    expect($checkout)->toBeInstanceOf(Checkout::class)
        ->and((string) $checkout)->toBe('https://checkout.paystack.test/redirect/REF_test123');

    Http::assertSent(fn($request) => str_contains($request->url(), '/transaction/initialize')
        && $request['plan'] === 'PLN_worid7k3e8v5afz');
});

it('maps success_url to the paystack callback_url and carries builder metadata', function () {
    fake_paystack();

    UserFactory::new()->create()
        ->newSubscription('primary', 'PLN_worid7k3e8v5afz')
        ->trialDays(5)
        ->checkout(['success_url' => 'https://app.test/done']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/transaction/initialize')
            && $request['callback_url'] === 'https://app.test/done'
            && $request['metadata']['subscription_name'] === 'primary'
            && $request['metadata']['trial_days'] === 5;
    });
});

it('cancels an existing active subscription when starting a new checkout', function () {
    fake_paystack();

    $user = UserFactory::new()->withPaystackId()->create();
    $old = SubscriptionFactory::new()->forUser($user)->create(['paystack_status' => Subscription::ACTIVE_STATUS]);

    $user->newSubscription('default', 'PLN_worid7k3e8v5afz')->checkout();

    expect($old->fresh()->paystack_status)->toBe(Subscription::CANCELLED_STATUS);
});

it('throws when the checkout cannot be started', function () {
    fake_paystack([
        'https://api.paystack.co/transaction/initialize*' => Http::response([], 500),
    ]);

    UserFactory::new()->create()->newSubscription('default', 'PLN_x')->checkout();
})->throws(RuntimeException::class);

it('redirects to paystack when the checkout is returned from a controller', function () {
    $response = (new Checkout('https://paystack.test/pay'))->toResponse(request());

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toBe('https://paystack.test/pay');
});

it('records the trial requested at checkout once the payment is verified', function () {
    fake_paystack([
        'https://api.paystack.co/transaction/verify/*' => Http::response(['status' => true, 'data' => [
            'status' => 'success',
            'reference' => 'REF_test123',
            'authorization' => ['card_type' => 'visa', 'last4' => '4081', 'exp_month' => '12', 'exp_year' => '2030'],
            'plan_object' => ['plan_code' => 'PLN_worid7k3e8v5afz', 'name' => 'Basic'],
            'metadata' => ['trial_days' => 7],
        ]]),
    ]);

    $user = UserFactory::new()->withPaystackId()->create();

    PaymentVerified::dispatch($user, 'REF_test123');

    expect(Subscription::where('user_id', $user->id)->first()->trial_ends_at)->not->toBeNull();
});
