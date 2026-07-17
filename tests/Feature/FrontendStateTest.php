<?php

use Collector\FrontendState;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

it('builds monthly and yearly plan collections with formatted prices', function () {
    fake_paystack();

    $state = app(FrontendState::class)->current('user', UserFactory::new()->create());

    expect($state)->toHaveKeys(['collectable', 'monthlyPlans', 'yearlyPlans', 'cancelation']);

    $basic = collect($state['monthlyPlans'])->firstWhere('id', 'PLN_worid7k3e8v5afz');

    expect($basic)->not->toBeNull()
        ->and($basic->currency)->toBe('NGN')
        // 500000 minor units -> 5,000.00 -> the trailing ".00" is trimmed.
        ->and($basic->price)->not->toEndWith('.00')
        ->and($basic->price)->toContain('5,000');
});

it('separates plans by interval', function () {
    fake_paystack();

    $state = app(FrontendState::class)->current('user', UserFactory::new()->create());

    expect(collect($state['monthlyPlans'])->pluck('interval')->unique()->all())->toBe(['monthly'])
        ->and(collect($state['yearlyPlans'])->pluck('interval')->unique()->all())->toBe(['yearly']);
});

it('throws when a configured plan is missing from the paystack account', function () {
    fake_paystack([
        'https://api.paystack.co/plan*' => Http::response(['status' => true, 'data' => []]),
    ]);

    app(FrontendState::class)->current('user', UserFactory::new()->create());
})->throws(RuntimeException::class, 'does not exist in your PayStack account');
