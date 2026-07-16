<?php

use Collector\Collector;
use Collector\CollectorManager;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Framework compatibility
|--------------------------------------------------------------------------
|
| These tests run on every Laravel version in the CI matrix. Because the
| suite boots against the version under test, they act as a live guard that
| the package both declares and actually supports the running framework —
| including the latest release (Laravel 13).
|
*/

it('declares support for the running laravel version in composer.json', function () {
    $major = (int) explode('.', app()->version())[0];

    $composer = json_decode(
        file_get_contents(__DIR__ . '/../../composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR
    );

    foreach (['illuminate/support', 'illuminate/database', 'illuminate/http'] as $package) {
        expect($composer['require'][$package])->toContain("^{$major}.0");
    }
})->skip(
    fn() => version_compare(app()->version(), '10.0.0', '<'),
    'Only asserts against supported Laravel majors (10+).'
);

it('boots the package service provider on the running laravel version', function () {
    // The manager singleton is bound by CollectorServiceProvider::register().
    expect(app()->bound('collector.manager'))->toBeTrue()
        ->and(app('collector.manager'))->toBeInstanceOf(CollectorManager::class)
        ->and(Collector::getFacadeRoot())->toBeInstanceOf(CollectorManager::class);
});

it('registers the collector routes on the running laravel version', function () {
    $names = collect(Route::getRoutes()->getRoutes())
        ->map(fn($route) => $route->getName())
        ->filter()
        ->values();

    expect($names)->toContain('collector.portal')
        ->toContain('collector.new-subscription')
        ->toContain('collector.cancel-subscription');
});
