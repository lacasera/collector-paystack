<?php

namespace Collector\Tests;

use Collector\Collectable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TestUser extends Authenticatable
{
    use Collectable;

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
}
