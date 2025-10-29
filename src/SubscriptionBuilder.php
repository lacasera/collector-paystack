<?php

namespace Collector;

class SubscriptionBuilder
{
    protected $owner;

    protected $name;

    protected $items = [];

    public function __construct($owner, $name, $prices = [])
    {
        $this->owner = $owner;
        $this->name = strtolower($name);

        foreach ((array) $prices as $price) {
            $this->price($price);
        }
    }

    public function price(mixed $price, $quantity = 1)
    {
        $options = is_array($price) ? $price : ['price' => $price];
        $quantity = $price['quantity'] ?? $quantity;

        if (! is_null($quantity)) {
            $options['quantity'] = $quantity;
        }

        if (isset($options['price'])) {
            $this->items[$options['price']] = $options;
        } else {
            $this->items[] = $options;
        }

        return $this;
    }

    public function checkout(array $options)
    {
        // return
    }
}
