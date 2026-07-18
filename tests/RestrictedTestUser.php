<?php

namespace Collector\Tests;

/**
 * A collectable whose $fillable only covers the application's own columns.
 *
 * This mirrors what a real host application looks like — the Laravel skeleton
 * ships `$fillable = ['name', 'email', 'password']` — and is deliberately
 * stricter than {@see TestUser}, which lists every package column and so
 * cannot catch the package mass-assigning its own attributes away.
 */
class RestrictedTestUser extends TestUser
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
