<?php

namespace Workbench\App\Models;

use Collector\Collectable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Collectable;

    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The package schema + webhook handlers key subscriptions on `user_id`.
     */
    public function getForeignKey(): string
    {
        return 'user_id';
    }
}
