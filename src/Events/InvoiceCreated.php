<?php

namespace Collector\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $collectable;

    public $payload;

    /**
     * Create a new event instance.
     */
    public function __construct($collectable, $payload)
    {
        $this->collectable = $collectable;
        $this->payload = $payload;
    }
}
