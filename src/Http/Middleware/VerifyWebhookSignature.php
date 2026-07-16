<?php

namespace Collector\Http\Middleware;

use Closure;
use Collector\Exceptions\SignatureVerificationException;
use Collector\PayStack\WebhookSignature;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next)
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('x-paystack-signature'),
                config('collector.secret')
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $next($request);
    }
}
