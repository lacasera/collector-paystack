<?php

namespace App\Providers;

use App\Models\User;
use Collector\Collector;
use Collector\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class CollectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Collector::collectable(User::class)->resolve(function (Request $request) {
            return $request->user();
        });

        Collector::collectable(User::class)->authorize(function (User $collectable, Request $request) {
            return $request->user()
                && $request->user()->id == $collectable->id;
        });

        // Collector::collectable(User::class)->checkPlanEligibility(function (User $collectable, Plan $plan) {
        // if ($billable->projects > 5 && $plan->name == 'Basic') {
        //     throw ValidationException::withMessages([
        //         'plan' => 'You have too many projects for the selected plan.'
        //     ]);
        // }
        // });
    }
}
