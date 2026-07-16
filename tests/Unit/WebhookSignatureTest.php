<?php

use Collector\Exceptions\SignatureVerificationException;
use Collector\PayStack\WebhookSignature;

const SECRET = 'test_secret_key';

it('accepts a correctly signed payload', function () {
    $body = json_encode(['event' => 'charge.success']);
    $signature = hash_hmac('sha512', $body, SECRET);

    expect(WebhookSignature::verifyHeader($body, $signature, SECRET))->toBeTrue();
});

it('rejects a tampered payload', function () {
    $signature = hash_hmac('sha512', json_encode(['event' => 'charge.success']), SECRET);

    WebhookSignature::verifyHeader(json_encode(['event' => 'charge.failed']), $signature, SECRET);
})->throws(SignatureVerificationException::class);

it('rejects a signature made with the wrong secret', function () {
    $body = json_encode(['event' => 'charge.success']);
    $signature = hash_hmac('sha512', $body, 'attacker_secret');

    WebhookSignature::verifyHeader($body, $signature, SECRET);
})->throws(SignatureVerificationException::class);

it('rejects a missing (null) signature header without a type error', function () {
    WebhookSignature::verifyHeader(json_encode(['event' => 'charge.success']), null, SECRET);
})->throws(SignatureVerificationException::class);
