<?php

namespace Workbench\App\Providers;

use Collector\CollectorManager;
use Collector\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * A stable key so the session cookie survives across the served requests.
     */
    private const APP_KEY = 'base64:S2VlcFRoaXNLZXlTdGFibGVGb3JFMkVUZXN0aW5nMDA=';

    public function register(): void
    {
        config([
            'app.key' => self::APP_KEY,
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => dirname(__DIR__, 2) . '/database/database.sqlite',
            'session.driver' => 'cookie',
        ]);
    }

    public function boot(): void
    {
        // Point the package at the workbench user (set in boot so it wins over
        // the package's merged default config).
        config([
            'collector.secret' => 'e2e_secret_key',
            'collector.currency' => 'NGN',
            'collector.collectables.user.model' => User::class,
            'collector.middleware' => ['web', 'auth'],
        ]);

        CollectorManager::useCustomerModel(User::class);

        $this->fakePaystack();
        $this->registerE2ERoutes();
    }

    /**
     * Fake every PayStack endpoint the package calls, so the served app never
     * makes a real network request. Runs per request (artisan serve bootstraps
     * the framework each time), so the stubs apply to the whole flow.
     */
    private function fakePaystack(): void
    {
        $plan = ['plan_code' => 'PLN_worid7k3e8v5afz', 'name' => 'Basic'];

        $subscription = [
            'subscription_code' => 'SUB_e2e',
            'email_token' => 'tok_e2e',
            'status' => 'active',
            'amount' => 500000,
            'plan' => $plan,
            'most_recent_invoice' => ['period_end' => now()->addMonth()->toIso8601String()],
        ];

        $customer = [
            'customer_code' => 'CUS_e2e',
            'email' => 'e2e@example.com',
            'authorizations' => [[
                'card_type' => 'visa', 'last4' => '4081', 'exp_month' => '12', 'exp_year' => '2030',
            ]],
            'subscriptions' => [$subscription],
        ];

        Http::fake([
            'https://api.paystack.co/plan*' => Http::response(['status' => true, 'data' => $this->planCatalogue()]),
            'https://api.paystack.co/customer*' => Http::response(['status' => true, 'data' => $customer]),
            'https://api.paystack.co/transaction/initialize*' => Http::response(['status' => true, 'data' => [
                // Relative so the browser resolves it against the served origin
                // (host:port), standing in for PayStack's hosted checkout URL.
                'authorization_url' => '/e2e/paystack-checkout?reference=REF_E2E',
                'access_code' => 'access_e2e',
                'reference' => 'REF_E2E',
            ]]),
            'https://api.paystack.co/transaction/verify/*' => Http::response(['status' => true, 'data' => [
                'status' => 'success',
                'reference' => 'REF_E2E',
                'authorization' => $customer['authorizations'][0],
                'plan_object' => $plan,
            ]]),
            'https://api.paystack.co/subscription/disable*' => Http::response(['status' => true, 'data' => ['message' => 'ok']]),
            'https://api.paystack.co/subscription/*' => Http::response(['status' => true, 'data' => $subscription]),
        ]);
    }

    /**
     * The catalogue must contain every plan code configured in collector.php,
     * otherwise FrontendState throws when building the portal.
     */
    private function planCatalogue(): array
    {
        return collect([
            'PLN_worid7k3e8v5afz' => 500000,
            'PLN_2y8oe4r1gx7gakr' => 5400000,
            'PLN_wc54sx7clavvy6d' => 1500000,
            'PLN_b9kvd76fufw4vu9' => 16200000,
            'PLN_g47cv05s5jsz29k' => 3000000,
            'PLN_l2qz2ab1wjhh4yx' => 6000000,
        ])->map(fn($amount, $code) => [
            'plan_code' => $code,
            'amount' => $amount,
            'currency' => 'NGN',
        ])->values()->all();
    }

    /**
     * Test-only routes that stand in for authentication and the PayStack
     * hosted checkout page so Playwright can drive the full flow.
     */
    private function registerE2ERoutes(): void
    {
        Route::middleware('web')->group(function () {
            // Target for the `auth` middleware redirect when unauthenticated.
            Route::get('/login', fn() => response('Please log in'))->name('login');

            Route::get('/e2e/login', function () {
                $user = User::firstOrCreate(
                    ['email' => 'e2e@example.com'],
                    ['name' => 'E2E User', 'password' => bcrypt('password')]
                );

                Auth::login($user);

                return redirect()->route('collector.portal');
            });

            // Stands in for PayStack's hosted checkout: bounce straight back to
            // the portal with a payment reference, as PayStack's callback would.
            Route::get('/e2e/paystack-checkout', function (Request $request) {
                return redirect()->route('collector.portal', ['reference' => $request->query('reference', 'REF_E2E')]);
            });

            // Seed an active subscription directly (no ?reference on the portal
            // URL), so the cancel flow can be asserted without a reload
            // re-triggering payment verification.
            Route::get('/e2e/subscribe', function () {
                $user = Auth::user();
                $user->forceFill(['paystack_id' => 'CUS_e2e'])->save();

                Subscription::query()->updateOrCreate(
                    ['paystack_id' => 'SUB_e2e'],
                    [
                        'user_id' => $user->id,
                        'name' => 'Basic',
                        'paystack_status' => 'active',
                        'paystack_plan' => 'PLN_worid7k3e8v5afz',
                        'paystack_email_token' => 'tok_e2e',
                        'quantity' => 1,
                    ]
                );

                // Straight to the management portal: the plans page would only
                // forward an active subscriber here anyway.
                return redirect()->route('collector.manage');
            });

            // Reset state between specs.
            Route::get('/e2e/reset', function () {
                Subscription::query()->delete();
                User::query()->update(['paystack_id' => null]);
                Auth::logout();

                return response('ok');
            });
        });
    }
}
