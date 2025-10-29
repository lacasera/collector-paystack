<?php

namespace Collector\PayStack;

trait ManagesPlans
{
    public function plans()
    {
        $plans = $this->request
            ->get('/plan')
            ->json('data');

        return collect($plans);
    }
}
