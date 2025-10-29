<?php

namespace Collector;

use Illuminate\Support\Facades\Auth;

trait RetrieveCollectableModels
{
    public function collectable($type = null, $id = null)
    {
        $type = $type ?: request('billableType');

        $id = $id ?: request('billableId') ?: Auth::id();

        if (! Collector::collectableModel($type) || ! $collectable = Collector::collectableModel($type)::find($id)) {
            abort(404);
        }

        if (! Collector::isAuthorizedToViewBillingPortal($collectable, request())) {
            abort(403);
        }

        if (! in_array(Collectable::class, class_uses_recursive($collectable))) {
            throw new \RuntimeException('Class [' . get_class($collectable) . '] does not use the [Collector\Collectable] trait.');
        }

        return $collectable;
    }
}
