# Changelog

All notable changes to `collector-paystack` will be documented in this file.

## 1.0.0 - 2026-07-21

First public release.

> Earlier versions appeared in this file but were never tagged or published to
> Packagist, so nothing was ever installable at those numbers. Those entries
> described unreleased development and have been folded into this release.

### Requirements

- PHP 8.2+ (8.3+ on Laravel 13)
- Laravel 12 or 13
- `ext-intl`
- A PayStack account and secret key

### Billing portal

- A React billing portal at `/collector/billing` listing the plans configured in
  `config/collector.php`, with a monthly/yearly toggle and trial support
- The portal URL is configurable through `collector.prefix`, `collector.path`
  and `collector.domain`. The webhook keeps its own `collector.webhook_path`, so
  relocating the portal cannot break an endpoint already registered with PayStack
- Subscribers are forwarded to the management portal; `?change=1` reaches the
  plan grid for switching plans
- The portal ships pre-built and inlined, so no build step is required in the
  host application. It takes its name, URLs and flash messages from the server
  at request time rather than from anything compiled into the bundle

### Subscription management portal

- `/collector/billing/manage` (`route('collector.manage')`) covering the current
  plan, payment history, stored payment methods and subscription history
- The open section is driven by `?section=`, so a reload keeps its place and
  applications can deep-link: `route('collector.manage', ['section' => 'methods'])`
- Card updates are delegated to PayStack's own hosted page through a short-lived
  link, so card details never reach the host application
- Cancelling lives here, and a cancelled subscription stays visible for the rest
  of its grace period showing the date access ends

### Subscriptions API

- Fluent, Cashier-style builder:
  `newSubscription($name, $plan)->trialDays(...)->checkout([...])`, returning a
  `Checkout` that can be returned from a controller or cast to a URL
- Cashier-parity helpers on the collectable: `subscribed()`, `subscribedToPlan()`,
  `subscribedToProduct()`, `createAsPayStackCustomer()`, `updatePayStackCustomer()`,
  `paymentMethods()`, `defaultPaymentMethod()`, `hasPaymentMethod()`,
  `billingPortalUrl()`, `currentActivePlan()` and `currentSubscription()`
- `syncSubscriptions()` reconciles the local table against PayStack, importing
  subscriptions started or cancelled outside the portal. Because PayStack has no
  plan-change endpoint, switching plans cancels the existing subscription and
  starts a new checkout — the reconciliation is what stops a subscription that is
  missing locally from surviving that switch and billing alongside its replacement
- `payStackTransactions()` for paginated payment history
- Model configuration via `Collector::useCustomerModel()` and
  `Collector::useSubscriptionModel()`; custom resolution via
  `Collector::collectable()->resolve(...)`
- Trial periods, grace periods, and events for the subscription lifecycle

### Webhooks

- A signed webhook endpoint handling `subscription.create`,
  `subscription.not_renew`, `charge.success`, `invoice.create` and
  `invoice.payment_failed`
- Signatures are compared in constant time (`hash_equals`); an invalid signature
  returns `403`
- Payment verification does not re-run when the checkout callback URL is reloaded

### Tooling

- `php artisan collector:install` publishes the config and assets and runs the
  migrations
- Test suite covering Pest feature/unit tests, Vitest component tests and
  Playwright end-to-end tests driving the real portal
- CI for the test matrix, coverage, the frontend, and end-to-end runs
- `.gitattributes` keeps the test suite, workbench application and frontend
  toolchain out of the distributed package
