<?php

namespace Collector\PayStack;

use Illuminate\Support\Collection;

trait ManagesTransactions
{
    /**
     * Fetch a page of this customer's PayStack transactions.
     *
     * PayStack filters transactions by the customer's numeric id (not the
     * CUS_ code), so the customer is resolved first. Returns the rows plus
     * PayStack's pagination meta so callers can page without guessing.
     *
     * @return array{data: Collection, meta: array}
     */
    public function payStackTransactions(int $page = 1, int $perPage = 20): array
    {
        $empty = ['data' => collect(), 'meta' => ['total' => 0, 'page' => $page, 'perPage' => $perPage, 'pageCount' => 0]];

        if (! $this->hasPayStackId()) {
            return $empty;
        }

        $customerId = data_get($this->getAsPaystackCustomer(), 'id');

        if (! $customerId) {
            return $empty;
        }

        $response = $this->request->get('/transaction', [
            'customer' => $customerId,
            'perPage' => $perPage,
            'page' => $page,
        ]);

        if (! $response->ok()) {
            return $empty;
        }

        return [
            'data' => collect($response->json('data') ?? []),
            'meta' => (array) ($response->json('meta') ?? $empty['meta']),
        ];
    }
}
