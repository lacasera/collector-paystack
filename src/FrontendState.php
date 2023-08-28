<?php

namespace Collector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

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
                throw new \RuntimeException('Plan ['.$plan->id.'] does not exist in your PayStack account.');
            }

            $plan->rawPrice = $paystackPrice['amount'];

            $price = $this->formatAmount($paystackPrice['amount']);

            if (Str::endsWith($price, '.00')) {
                $price = substr($price, 0, -3);
            }

            if (Str::endsWith($price, '.0')) {
                $price = substr($price, 0, -2);
            }

            $plan->price = $price;

            $plan->currency = config('collector.currency');

            return $plan;
        });
    }

    private function formatAmount($amount)
    {
        $currency = strtoupper(config('collector.currency'));

        $money = new Money($amount, new Currency($currency));

        $numberFormatter = new NumberFormatter('en', NumberFormatter::CURRENCY);

        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($money);
    }
}
