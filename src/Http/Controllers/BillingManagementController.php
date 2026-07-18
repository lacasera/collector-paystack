<?php

namespace Collector\Http\Controllers;

use Collector\Collector;
use Collector\GuessCollectableTypes;
use Collector\Models\Subscription;
use Collector\MoneyFormatter;
use Collector\RetrieveCollectableModels;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;

/**
 * The subscription management portal: current plan, payment history, stored
 * payment methods and subscription history.
 */
class BillingManagementController
{
    use GuessCollectableTypes;
    use RetrieveCollectableModels;

    /**
     * The sections the portal can open on.
     *
     * Driven by `?section=` so a reload keeps its place and applications can
     * deep-link, e.g. `route('collector.manage', ['section' => 'history'])`.
     */
    public const SECTIONS = ['overview', 'history', 'methods', 'subscriptions'];

    public function __invoke(Request $request, $type = null, $id = null)
    {
        $type = $type ?: $this->guessCollectableType();

        $collectable = $this->collectable($type, $id);

        // PayStack is the source of truth. Reconciling on load keeps the page
        // honest when a subscription was started or cancelled elsewhere.
        if ($collectable->hasPayStackId()) {
            $collectable->syncSubscriptions();
        }

        $page = max(1, (int) $request->query('page', 1));

        $transactions = $collectable->payStackTransactions($page);

        Inertia::setRootView('collector::app');

        View::share([
            'cssPath' => __DIR__ . '/../../../public/css/collector.css',
            'jsPath' => __DIR__ . '/../../../public/js/app.js',
        ]);

        return Inertia::render('Manage', [
            'section' => $this->section($request),
            'collectable' => ['email' => $collectable->email],
            'currentPlan' => $this->currentPlan($type, $collectable),
            'paymentMethods' => $this->paymentMethods($collectable),
            'transactions' => [
                'data' => $this->transactions($transactions['data']),
                'meta' => $transactions['meta'],
            ],
            'subscriptions' => $this->subscriptions($type, $collectable),
            'cancelation' => config('collector.cancelation'),
        ]);
    }

    /**
     * The section to open on, falling back to the overview.
     *
     * An unknown value is ignored rather than erroring — a stale bookmark or a
     * typo in a redirect should still land somewhere useful.
     */
    private function section(Request $request): string
    {
        $section = (string) $request->query('section');

        return in_array($section, self::SECTIONS, true) ? $section : 'overview';
    }

    /**
     * The plan the collectable is currently subscribed to, decorated with the
     * configured display details and PayStack's next billing date.
     */
    private function currentPlan($type, $collectable): ?array
    {
        // Includes a cancelled subscription still within its grace period, so
        // the customer can see how long their access runs for.
        if (! $subscription = $collectable->currentSubscription()) {
            return null;
        }

        $configured = Collector::plans($type)->firstWhere('id', $subscription->paystack_plan);

        $remote = $collectable->fetchSubscription($subscription->paystack_id);

        return [
            'code' => $subscription->paystack_id,
            'planCode' => $subscription->paystack_plan,
            'name' => $configured->name ?? $subscription->name,
            'description' => $configured->description ?? null,
            'features' => $configured->features ?? [],
            'interval' => $configured->interval ?? null,
            'amount' => is_null($amount = data_get($remote, 'plan.amount'))
                ? null
                : MoneyFormatter::format($amount, data_get($remote, 'plan.currency')),
            'card' => data_get($remote, 'authorization.last4') ? [
                'brand' => trim((string) data_get($remote, 'authorization.card_type')),
                'last4' => data_get($remote, 'authorization.last4'),
            ] : null,
            'nextPaymentDate' => $this->date(data_get($remote, 'next_payment_date')),
            'onTrial' => $subscription->onTrial(),
            'trialEndsAt' => $this->date($subscription->trial_ends_at),
            'onGracePeriod' => $subscription->onGracePeriod(),
            'endsAt' => $this->date($subscription->ends_at),
        ];
    }

    /**
     * Cards PayStack has stored for this customer.
     *
     * PayStack records an authorization per transaction, so paying repeatedly
     * with the same card yields duplicates. Its `signature` identifies the
     * underlying card, which collapses them back into one entry.
     */
    private function paymentMethods($collectable): array
    {
        return $collectable->paymentMethods()
            ->unique(fn($method) => data_get($method, 'signature')
                ?: data_get($method, 'last4') . data_get($method, 'exp_month') . data_get($method, 'exp_year'))
            ->map(fn($method) => [
                'brand' => trim((string) data_get($method, 'card_type')),
                'last4' => data_get($method, 'last4'),
                'expiry' => data_get($method, 'exp_month') . '/' . data_get($method, 'exp_year'),
                'bank' => data_get($method, 'bank'),
                'channel' => data_get($method, 'channel'),
                'reusable' => (bool) data_get($method, 'reusable'),
            ])
            ->values()
            ->all();
    }

    /**
     * Payment history, straight from PayStack's transaction list.
     */
    private function transactions($transactions): array
    {
        return $transactions
            ->map(fn($transaction) => [
                'reference' => data_get($transaction, 'reference'),
                'amount' => is_null($amount = data_get($transaction, 'amount'))
                    ? null
                    : MoneyFormatter::format($amount, data_get($transaction, 'currency')),
                'status' => data_get($transaction, 'status'),
                'channel' => data_get($transaction, 'channel'),
                'paidAt' => $this->date(data_get($transaction, 'paid_at') ?: data_get($transaction, 'created_at')),
                'brand' => trim((string) data_get($transaction, 'authorization.card_type')),
                'last4' => data_get($transaction, 'authorization.last4'),
            ])
            ->values()
            ->all();
    }

    /**
     * Every subscription recorded for the collectable, newest first.
     */
    private function subscriptions($type, $collectable): array
    {
        $plans = Collector::plans($type);

        return $collectable->subscriptions()
            ->get()
            ->map(fn(Subscription $subscription) => [
                'code' => $subscription->paystack_id,
                'name' => $plans->firstWhere('id', $subscription->paystack_plan)->name ?? $subscription->name,
                'status' => $subscription->paystack_status,
                'active' => $subscription->isActive(),
                'startedAt' => $this->date($subscription->created_at),
                'endsAt' => $this->date($subscription->ends_at),
                'cancelationReason' => $subscription->cancelation_reason,
            ])
            ->values()
            ->all();
    }

    private function date($value): ?string
    {
        return $value ? Carbon::parse($value)->format('M j, Y') : null;
    }
}
