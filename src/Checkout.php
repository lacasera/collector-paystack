<?php

namespace Collector;

use Illuminate\Contracts\Support\Responsable;

/**
 * Wraps a PayStack hosted-checkout authorization URL.
 *
 * Returning it from a controller redirects the user to PayStack; casting it to a
 * string (or reading ->url) yields the raw URL for JSON/API responses.
 */
class Checkout implements Responsable
{
    public function __construct(public readonly string $url) {}

    public function redirect(): string
    {
        return $this->url;
    }

    public function toResponse($request)
    {
        return redirect()->away($this->url);
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
