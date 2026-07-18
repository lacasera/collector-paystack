<?php

namespace Collector;

use Illuminate\Support\Str;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

class MoneyFormatter
{
    /**
     * Format a PayStack minor-unit amount for display.
     *
     * PayStack returns amounts in the currency's smallest unit (2000 = GHS 20),
     * and the portal shows whole amounts without trailing zero decimals.
     */
    public static function format(?int $amount, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?: config('collector.currency'));

        $money = new Money($amount ?? 0, new Currency($currency));

        // Follow the application's locale so amounts are grouped and symbolised
        // the way the rest of the host application formats numbers.
        $numberFormatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);

        $formatted = (new IntlMoneyFormatter($numberFormatter, new ISOCurrencies()))->format($money);

        if (Str::endsWith($formatted, '.00')) {
            $formatted = substr($formatted, 0, -3);
        }

        if (Str::endsWith($formatted, '.0')) {
            $formatted = substr($formatted, 0, -2);
        }

        return $formatted;
    }
}
