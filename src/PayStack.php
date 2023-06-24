<?php

namespace Collector;

use Collector\PayStack\ManagesCustomer;
use Collector\PayStack\ManagesPlans;
use Collector\PayStack\ManagesSubscription;
use Collector\PayStack\PrepareRequest;
use Illuminate\Http\Client\PendingRequest;

trait PayStack
{
    protected PendingRequest $request;

    use ManagesPlans;
    use ManagesCustomer;
    use ManagesSubscription;

    public function paystack()
    {
        $this->request = PrepareRequest::prepare();

        return $this;
    }
}
