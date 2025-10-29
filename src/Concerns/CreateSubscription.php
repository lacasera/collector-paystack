<?php

namespace Collector\Concerns;

interface CreateSubscription
{
    /**
     * @param  array  $options
     * @return mixed
     */
    public function create($collectable, $plan, $options = []);
}
