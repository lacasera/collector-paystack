<?php

namespace Collector\PayStack;

use Collector\Exceptions\SignatureVerificationException;

class WebhookSignature
{
    public static function verifyHeader($data, $signature, $secret)
    {
        $expected = hash_hmac('sha512', $data, $secret);

        if (! is_string($signature) || ! hash_equals($expected, $signature)) {
            throw new SignatureVerificationException('Unable to verify paystack signature');
        }

        return true;
    }
}
