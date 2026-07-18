<?php

namespace Collector;

use Illuminate\Database\Eloquent\Model;

class FrontendState
{
    /**
     * @return array
     */
    public function current($type, Model $collectable)
    {
        $plans = $this->getPlans($type, $collectable);

        return [
            'collectable' => $collectable->toArray(),
            'monthlyPlans' => $plans->where('interval', 'monthly')->where('active', true)->values(),
            'yearlyPlans' => $plans->where('interval', 'yearly')->where('active', true)->values(),
            'cancelation' => config('collector.cancelation'),
        ];
    }

    protected function getPlans($type, $collectable)
    {
        $plans = Collector::plans($type);

        $prices = $collectable->paystack()->plans();

        return $plans->map(function ($plan) use ($prices) {
            if (! $paystackPrice = $prices->firstWhere('plan_code', $plan->id)) {
                throw new \RuntimeException('Plan [' . $plan->id . '] does not exist in your PayStack account.');
            }

            $plan->rawPrice = $paystackPrice['amount'];

            $plan->price = MoneyFormatter::format($paystackPrice['amount']);

            $plan->currency = config('collector.currency');

            return $plan;
        });
    }
}
