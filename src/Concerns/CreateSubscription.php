<?php

namespace Collector\Concerns;


interface CreateSubscription
{
    /**
     * @param $collectable
     * @param $plan
     * @param array $options
     * @return mixed
     */
    public function create($collectable, $plan,  $options = []);
}