<?php

namespace Collector;

class CollectableConfigBuilder
{
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function resolve(callable $callback)
    {
        Collector::resolveCollectableUsing($this->type, $callback);

        return $this;
    }

    public function authorize(callable $callback)
    {
        Collector::authorizeUsing($this->type, $callback);

        return $this;
    }
}
