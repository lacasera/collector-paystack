<?php

namespace Collector\PayStack;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PrepareRequest
{
    /**
     * @var string
     */
    protected static $baseUrl = 'https://api.paystack.co';

    /**
     * @return PendingRequest
     */
    public static function prepare(): PendingRequest
    {
        return Http::asJson()
            ->withToken(env('PAYSTACK_SECRET_KEY'))
            ->baseUrl(static::$baseUrl);
    }
}
