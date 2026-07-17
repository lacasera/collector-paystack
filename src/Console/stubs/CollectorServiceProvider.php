<?php

namespace App\Providers;

use App\Models\User;
use Collector\Collector;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

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
    }
}
