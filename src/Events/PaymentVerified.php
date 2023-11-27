<?php

namespace Collector\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
