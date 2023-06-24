<?php

namespace Collector;

trait GuessCollectableTypes
{
    protected function guessCollectableType()
    {
        if (count(config('collector.collectables')) == 1) {
            return array_keys(config('collector.collectables'))[0];
        }

        return 'user';
    }
}
