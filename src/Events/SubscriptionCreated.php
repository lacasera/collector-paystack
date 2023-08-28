<?php

namespace Collector\Events;

class SubscriptionCreated
{
    public $collectable;

    public $reference;

    /**
     * Create a new event instance.
     */
    public function __construct($collectable, $reference)
    {
        $this->collectable = $collectable;
        $this->reference = $reference;
    }
}
