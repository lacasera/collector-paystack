<?php

namespace Collector\Http\Middleware;

use Closure;
use Collector\Exceptions\SignatureVerificationException;
use Collector\PayStack\WebhookSignature;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class VerifyWebhookSignature extends Middleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('X_PAYSTACK_SIGNATURE'),
                config('collector.secret')
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedException($exception->getMessage(), $exception->getCode());
        }

        return $next($request);
    }
}
