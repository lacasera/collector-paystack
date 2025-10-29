<?php

namespace Collector\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCanceled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $collectable;

    public $subscription;

    /**
     * Create a new event instance.
     */
    public function __construct($collectable, $subscription)
    {
        $this->collectable = $collectable;
        $this->subscription = $subscription;
    }
}
