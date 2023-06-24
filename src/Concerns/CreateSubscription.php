<?php

namespace Collector\Concerns;

use Collector\Collectable;

interface CreateSubscription
{
    /**
     * @param Collectable $collectable
     * @param $plan
     * @param $options
     * @return mixed
     */
    public function create(Collectable $collectable, $plan, $options = []);
}