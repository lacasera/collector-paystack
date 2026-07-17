<?php

use Collector\Collector;

function definePlans(array $plans): void
{
    config()->set('collector.collectables.test.plans', $plans);
}

it('expands each configured plan into one entry per priced interval', function () {
    definePlans([
        ['name' => 'Pro', 'description' => 'd', 'features' => [], 'monthly_id' => 'PLN_m', 'yearly_id' => 'PLN_y'],
    ]);

    $plans = Collector::plans('test');

    expect($plans)->toHaveCount(2)
        ->and($plans->pluck('interval')->all())->toEqualCanonicalizing(['monthly', 'yearly'])
        ->and($plans->pluck('id')->all())->toEqualCanonicalizing(['PLN_m', 'PLN_y']);
});

it('skips intervals that have no plan id configured', function () {
    definePlans([
        ['name' => 'Solo', 'description' => 'd', 'features' => [], 'monthly_id' => 'PLN_m'],
    ]);

    $plans = Collector::plans('test');

    expect($plans)->toHaveCount(1)
        ->and($plans->first()->interval)->toBe('monthly');
});

it('marks archived plans as inactive', function () {
    definePlans([
        ['name' => 'Old', 'description' => 'd', 'features' => [], 'monthly_id' => 'PLN_m', 'archived' => true],
        ['name' => 'New', 'description' => 'd', 'features' => [], 'monthly_id' => 'PLN_n'],
    ]);

    $plans = Collector::plans('test');

    expect($plans->firstWhere('id', 'PLN_m')->active)->toBeFalse()
        ->and($plans->firstWhere('id', 'PLN_n')->active)->toBeTrue();
});

it('carries plan metadata (description, features, incentives) onto each entry', function () {
    definePlans([
        [
            'name' => 'Pro',
            'description' => 'Best plan',
            'features' => ['A', 'B'],
            'monthly_id' => 'PLN_m',
            'yearly_id' => 'PLN_y',
            'yearly_incentive' => 'Save 20%',
        ],
    ]);

    $yearly = Collector::plans('test')->firstWhere('interval', 'yearly');

    expect($yearly->description)->toBe('Best plan')
        ->and($yearly->features)->toBe(['A', 'B'])
        ->and($yearly->yearlyIncentive)->toBe('Save 20%');
});
