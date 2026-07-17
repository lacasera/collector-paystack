<?php

namespace Collector\Http\Controllers;

use Collector\Actions\SyncSubscription;
use Collector\Collector;
use Collector\Events\InvoiceCreated;
use Collector\Events\PaymentReceived;
use Collector\Events\SubscriptionCanceled;
use Collector\Events\WebhookReceived;
use Collector\Http\Middleware\VerifyWebhookSignature;
use Collector\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CollectorWebhookController extends Controller
{
    public function __construct()
    {
        if (config('collector.secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['event']));

        WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            $this->{$method}($payload);
        }

        return response()->json();
    }

    protected function handleSubscriptionCreate(array $payload): void
    {
        $user = Collector::findCollectable(data_get($payload, 'data.customer.email'));

        if (! $user) {
            return;
        }

        // Persist the PayStack customer code from the payload so the sync action
        // can fetch the customer's subscriptions to locate this one.
        if (! $user->hasPayStackId()) {
            $user->forceFill([
                'paystack_id' => data_get($payload, 'data.customer.customer_code'),
            ])->save();
        }

        app(SyncSubscription::class)->sync($user, data_get($payload, 'data.plan.plan_code'));
    }

    protected function handleSubscriptionNotRenew(array $payload): void
    {
        $user = Collector::findCollectable(data_get($payload, 'data.customer.customer_code'));

        if (! $user) {
            return;
        }

        if (! $user->hasActivePlan(data_get($payload, 'data.plan.plan_code'))) {
            return;
        }

        $subscription = Subscription::$subscriptionModel::where(
            'paystack_id',
            data_get($payload, 'data.subscription_code')
        )->first();

        SubscriptionCanceled::dispatch($user, $subscription);
    }

    protected function handleChargeSuccess(array $payload): void
    {
        $user = Collector::findCollectable(data_get($payload, 'data.customer.customer_code'));

        if (! $user) {
            return;
        }

        PaymentReceived::dispatch($user, $payload);
    }

    protected function handleInvoiceCreate(array $payload): void
    {
        $user = Collector::findCollectable(data_get($payload, 'data.customer.customer_code'));

        if (! $user) {
            return;
        }

        InvoiceCreated::dispatch($user, $payload);
    }

    protected function handleInvoicePaymentFailed(array $payload): void
    {
        $user = Collector::findCollectable(data_get($payload, 'data.customer.customer_code'));

        if (! $user) {
            return;
        }

        PaymentReceived::dispatch($user, $payload);
    }
}
