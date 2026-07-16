<?php

use Collector\Tests\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

it('returns transaction data when verification succeeds', function () {
    fake_paystack();

    $data = UserFactory::new()->create()->completedTransaction('REF_test123');

    expect($data)->not->toBeNull()
        ->and($data['status'])->toBe('success');
});

it('returns null when the transaction did not succeed', function () {
    // Regression: the old `! data_get(...) === 'success'` precedence bug meant a
    // failed transaction was still treated as completed. It must now return null.
    fake_paystack([
        'https://api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'failed', 'reference' => 'REF_test123'],
        ]),
    ]);

    expect(UserFactory::new()->create()->completedTransaction('REF_test123'))->toBeNull();
});
