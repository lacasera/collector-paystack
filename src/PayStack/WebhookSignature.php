<?php

namespace Collector\PayStack;

use Collector\Exceptions\SignatureVerificationException;

class WebhookSignature
{
    public static function verifyHeader($data, $signature, $secret)
    {
        if ($signature !== hash_hmac('sha512', $data, $secret)) {
            throw new SignatureVerificationException('Unable to verify paystack signature');
        }

        return true;
    }
}
