<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Collector\Http\Middleware\HandleInertiaRequests;

if (! function_exists('paystack_fixture')) {
    /**
     * Load a Paystack JSON fixture as an associative array.
     *
     * Names are relative to tests/Fixtures/Paystack, without the .json
     * extension, e.g. "customer" or "webhooks/charge-success".
     */
    function paystack_fixture(string $name): array
    {
        $path = __DIR__ . '/Fixtures/Paystack/' . $name . '.json';

        if (! is_file($path)) {
            throw new InvalidArgumentException("Missing Paystack fixture [{$name}] at {$path}.");
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }
}

if (! function_exists('fake_paystack')) {
    /**
     * Register HTTP fakes for the Paystack endpoints the package touches.
     *
     * Pass $overrides to replace individual endpoint responses per-test
     * (e.g. to simulate a failed customer creation or a declined charge).
     */
    function fake_paystack(array $overrides = []): void
    {
        // Ordering matters: Http::fake returns the first pattern that matches,
        // so narrower URLs must be listed before the broader ones.
        Http::fake(array_merge([
            'https://api.paystack.co/customer*' => Http::response([
                'status' => true,
                'data' => paystack_fixture('customer'),
            ]),
            'https://api.paystack.co/subscription/*/manage/link' => Http::response([
                'status' => true,
                'data' => ['link' => 'https://paystack.com/manage/subscriptions/test123?subscription_token=tok'],
            ]),
            'https://api.paystack.co/subscription?*' => Http::response([
                'status' => true,
                'data' => [],
                'meta' => ['total' => 0, 'perPage' => 100, 'page' => 1, 'pageCount' => 1],
            ]),
            'https://api.paystack.co/transaction?*' => Http::response([
                'status' => true,
                'data' => [paystack_fixture('transaction-verify')],
                'meta' => ['total' => 1, 'perPage' => 20, 'page' => 1, 'pageCount' => 1],
            ]),
            'https://api.paystack.co/plan*' => Http::response([
                'status' => true,
                'data' => paystack_fixture('plans'),
            ]),
            'https://api.paystack.co/transaction/initialize*' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.test/redirect/REF_test123',
                    'access_code' => 'access_test123',
                    'reference' => 'REF_test123',
                ],
            ]),
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => paystack_fixture('transaction-verify'),
            ]),
            'https://api.paystack.co/subscription/disable*' => Http::response([
                'status' => true,
                'data' => ['message' => 'Subscription disabled successfully'],
            ]),
            'https://api.paystack.co/subscription/*' => Http::response([
                'status' => true,
                'data' => paystack_fixture('subscription'),
            ]),
        ], $overrides));
    }
}

if (! function_exists('paystack_signature')) {
    /**
     * Compute the PayStack webhook signature for a raw request body.
     */
    function paystack_signature(string $body, ?string $secret = null): string
    {
        return hash_hmac('sha512', $body, $secret ?? config('collector.secret'));
    }
}

if (! function_exists('paystack_webhook_headers')) {
    /**
     * Build request headers carrying a valid PayStack webhook signature.
     */
    function paystack_webhook_headers(string $body, ?string $secret = null): array
    {
        return [
            'X-Paystack-Signature' => paystack_signature($body, $secret),
        ];
    }
}

if (! function_exists('inertia_headers')) {
    /**
     * Headers that make a request an Inertia XHR visit (JSON response, no root
     * view render), with the collector middleware's asset version so the visit
     * is not answered with a 409 version conflict.
     */
    function inertia_headers(): array
    {
        $version = (new HandleInertiaRequests())->version(request());

        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
        ];
    }
}

if (! function_exists('post_webhook')) {
    /**
     * POST a webhook payload to the collector endpoint with a signature header.
     *
     * Sends the raw JSON body so the signature covers exactly what the
     * middleware verifies. Pass $signature to override with an invalid value.
     */
    function post_webhook(array $payload, ?string $signature = null): TestResponse
    {
        $body = json_encode($payload);

        $signature ??= paystack_signature($body);

        return test()->call('POST', route('collector.webhook'), [], [], [], [
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }
}
