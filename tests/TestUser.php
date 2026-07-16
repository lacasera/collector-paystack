<?php

namespace Collector\Tests;

use Collector\Collectable;
use Collector\Tests\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TestUser extends Authenticatable
{
    use Collectable;
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'paystack_id',
        'pm_type',
        'pm_last_four',
        'pm_expiration',
        'extra_billing_information',
        'trial_ends_at',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'vat_id',
        'receipt_emails',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The package schema + webhook handlers key subscriptions on `user_id`
     * (matching the real App\Models\User). Pin the test model's foreign key so
     * the Subscription relationships resolve regardless of this class name.
     */
    public function getForeignKey(): string
    {
        return 'user_id';
    }
}
