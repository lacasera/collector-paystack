<?php

use Collector\Collector;
use Collector\Tests\Factories\UserFactory;
use Collector\Tests\TestUser;

it('returns null when no custom resolver is registered', function () {
    expect(Collector::resolveCollectable('user', request()))->toBeNull();
});

it('uses a registered custom collectable resolver', function () {
    $expected = UserFactory::new()->create();

    Collector::resolveCollectableUsing('user', fn($request) => $expected);

    expect(Collector::resolveCollectable('user', request())->is($expected))->toBeTrue();
});

it('registers a resolver through the collectable config builder', function () {
    $expected = UserFactory::new()->create();

    Collector::collectable(TestUser::class)
        ->resolve(fn($request) => $expected);

    expect(Collector::resolveCollectable('user', request())->is($expected))->toBeTrue();
});
