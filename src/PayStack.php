<?php

namespace Collector;

use Collector\PayStack\PrepareRequest;
use Illuminate\Http\Client\PendingRequest;

trait PayStack
{
    protected PendingRequest $request;

    public function paystack()
    {
        $this->request = PrepareRequest::prepare();

        return $this;
    }
}
