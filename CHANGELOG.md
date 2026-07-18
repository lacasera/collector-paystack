# Changelog

All notable changes to `collector-paystack` will be documented in this file.

## Unreleased

### Added
- **Subscription management portal** at `/collector/billing/manage`
  (`route('collector.manage')`): current plan, payment history, stored payment
  methods and subscription history. The open section is driven by `?section=`,
  so a reload keeps its place and applications can deep-link, e.g.
  `route('collector.manage', ['section' => 'methods'])`
- Card updates are delegated to PayStack's own hosted page via a short-lived
  link (`subscriptionManageLink()`), so card details never reach the application
- `syncSubscriptions()` reconciles the local `subscriptions` table against
  PayStack, importing subscriptions started or cancelled outside the portal
- `payStackTransactions()` for paginated payment history, and
  `currentSubscription()`, which unlike `currentActivePlan()` also returns a
  cancelled subscription that is still inside its grace period
- **Configurable portal URL**: `collector.prefix`, `collector.path` and
  `collector.domain`. The webhook keeps its own `collector.webhook_path` so
  moving the portal cannot break an endpoint already registered with PayStack
- Laravel 13 and PHP 8.4 support (CI matrix + compatibility guard test)
- Fluent, Cashier-style subscription builder:
  `newSubscription($name, $plan)->trialDays(...)->checkout([...])`, returning a
  `Checkout` you can return from a controller (redirect) or cast to a URL string
- Cashier-parity helper methods on the collectable: `subscribed()`,
  `subscribedToPlan()`, `subscribedToProduct()`, `createAsPayStackCustomer()`,
  `updatePayStackCustomer()`, `paymentMethods()`, `defaultPaymentMethod()`,
  `hasPaymentMethod()`, and `billingPortalUrl()`
- Unified model configuration via `Collector::useCustomerModel()` and
  `Collector::useSubscriptionModel()` (a single call configures both the
  collectable and the `Subscription → owner` relationship)
- Idempotent subscription sync and plan-switch cancellation of the existing
  subscription
- Custom collectable resolution via `Collector::collectable()->resolve(...)`
- Full automated test suite: Pest feature/unit tests, Vitest React component
  tests, and Playwright end-to-end tests driving the real billing portal
- CI workflows for coverage (`pcov`, min gate), the frontend (type-check +
  Vitest + build), and E2E (Playwright against a Testbench workbench app)

### Fixed
- **Billing columns were silently discarded** on any host `User` model with a
  restrictive `$fillable` — the Laravel default. `paystack_id` was never
  persisted, so no subscription was ever recorded and no plan showed as current.
  The package now writes its own columns with `forceFill()`
- Subscribing repeatedly stacked concurrent PayStack subscriptions (and so
  concurrent billing), because the cancel-then-switch step could only see
  subscriptions present in the local table
- A subscription re-enabled on PayStack kept its old grace-period date and went
  on reporting itself as cancelled, which also hid the cancel action
- Subscription sync no longer aborts when PayStack returns a plan with no name
  (the `name` column is not nullable)
- `currentActivePlan()` is ordered, so the portal no longer highlights an
  arbitrary plan when more than one subscription is active
- The billing portal fetched the PayStack customer three times per page load
- Payment amounts that PayStack does not return render as "—" rather than a
  confident zero
- The management portal shows the card bound to the subscription rather than
  whichever stored authorization happened to come first
- Flash messages now reach the UI: the key written by the server did not match
  the one read by the middleware, and nothing rendered the prop. The message is
  also raised after payment is verified rather than before checkout begins
- The header no longer opens the host application's home page in an Inertia
  overlay
- **Security:** webhook signatures are now compared in constant time
  (`hash_equals`); invalid signatures return `403`
- Payment verification no longer re-runs when the checkout callback URL is
  reloaded (Post/Redirect/Get)
- The React portal now actually renders: the bundle is built as a single
  self-contained IIFE so it can be inlined, and the compiled `public/js/app.js`
  is shipped with the package (was gitignored)
- `Subscription::isActive()` reads the correct `paystack_status` column
- `completedTransaction()` correctly returns `null` for unsuccessful transactions
- `subscription.create` no longer errors for existing customers; `not_renew`
  looks subscriptions up by `paystack_id`
- Migration rollback works on SQLite (drops the index before the column)
- Corrected the billing portal CSS asset path

### Changed
- **Breaking:** subscribers visiting the plans page are forwarded to the
  management portal. Use `?change=1` (`collector.urls.changePlan`) to reach the
  plan grid for switching plans
- **Breaking:** cancelling moved off the plans page onto the management portal;
  the current plan's card now links there instead of cancelling in place
- Currency amounts follow the application locale instead of a hard-coded `en`
- `ValidPlan` migrated from the deprecated `Rule` interface to `ValidationRule`
- Extracted the duplicated webhook/listener subscription logic into a shared
  action

### Removed
- **Breaking:** unused config keys `prorates`, `features`, `date_format` and
  `receipt_data`. None were read anywhere, and `features` referenced a
  `Collector\Features` class that does not exist. PayStack has no proration or
  plan-change API, so `prorates` could never have had an effect
- An unreachable `Pages/Index.tsx` mockup that shipped fabricated payment data
  (a sample email, card number and invented receipts) inside the bundle
- Unused moment.js and lodash CDN scripts (~135KB fetched on every portal load)
- Dead code: the empty `CreateCustomer` action, `Subscription::swap()`, and
  unused config-builder methods

### Packaging
- Added `.gitattributes` so `composer require` no longer ships the test suite,
  the workbench application or the frontend toolchain into consumers' vendor
  directories
- Accurate package description and searchable keywords for Packagist;
  `minimum-stability` is now `stable`

### Docs
- Rewrote the README: accurate public API (the old `newSubscription()->create()`
  and `subscribed()` examples never existed), `collector:install`, events,
  authorization hooks, and webhook setup

## 2.2.0 - 2025-10-28

### Added
- Modern frontend tooling with ESLint and Prettier
- React 18.3+ with createRoot API
- Enhanced TypeScript configuration
- Utility functions for better code organization
- Modern Vite configuration with optimizations
- Enhanced Tailwind CSS with custom animations and colors

### Changed
- **FRONTEND MODERNIZATION**: Complete frontend stack upgrade
- Updated all frontend dependencies to latest versions
- Migrated to React 18 createRoot API from legacy render
- Improved component architecture with better error handling
- Enhanced Vite build configuration with hash-based filenames
- Modern ES modules throughout the frontend
- Better TypeScript support and configuration
- Improved development experience with better tooling

### Fixed
- React 18 compatibility issues
- Missing axios import in Plan component
- Proper error handling in subscription flow
- Modern JavaScript patterns and best practices

## 2.1.0 - 2025-10-28

### Added
- Laravel 12 support
- Orchestra Testbench 10.x support for Laravel 12 testing

### Changed
- Updated GitHub Actions workflow to test against Laravel 12
- Updated README to reflect Laravel 12 compatibility

## 2.0.0 - 2025-10-28

### Added
- Laravel 10/11 support
- PHP 8.2+ support
- Modern PHP features (constructor property promotion, typed properties, return types)
- Comprehensive test suite with PHPUnit 10/11
- GitHub Actions workflows for automated testing and code style fixing
- Vite build system replacing Laravel Mix
- Modern frontend dependencies
- Comprehensive README with usage examples

### Changed
- **BREAKING**: Minimum PHP version is now 8.2
- **BREAKING**: Minimum Laravel version is now 10.0
- Updated all dependencies to their latest stable versions
- Modernized codebase with strict typing and modern PHP patterns
- Replaced Laravel Mix with Vite for better build performance
- Updated React components to use modern patterns
- Improved service provider with better organization
- Enhanced Plan class with constructor property promotion

### Fixed
- Fixed typo in `ACTVIE_STATUS` constant (now `ACTIVE_STATUS`)
- Improved error handling and type safety throughout the codebase
- Fixed deprecation warnings for modern Laravel versions

### Removed
- Support for PHP < 8.2
- Support for Laravel < 10.0
- Laravel Mix configuration (replaced with Vite)

## 1.0.0 - 2023-XX-XX

- Initial release with basic PayStack integration
- Subscription management functionality
- React-based billing portal
