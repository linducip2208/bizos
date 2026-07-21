<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRateLog;

class CurrencyService
{
    public function convert(float $amount, string $fromCurrencyCode, string $toCurrencyCode, ?string $date = null): float
    {
        if ($fromCurrencyCode === $toCurrencyCode) {
            return $amount;
        }

        $rate = $this->getExchangeRate($fromCurrencyCode, $toCurrencyCode, $date);

        return round($amount * $rate, 6);
    }

    public function getExchangeRate(string $fromCode, string $toCode, ?string $date = null): float
    {
        $baseCurrency = Currency::base()->first();

        if (!$baseCurrency) {
            return 1;
        }

        $fromCurrency = Currency::where('code', $fromCode)->first();
        $toCurrency = Currency::where('code', $toCode)->first();

        if (!$fromCurrency || !$toCurrency) {
            return 1;
        }

        $fromRate = $this->resolveRate($fromCurrency, $baseCurrency, $date);
        $toRate = $this->resolveRate($toCurrency, $baseCurrency, $date);

        if ($fromRate == 0) {
            return 0;
        }

        return $toRate / $fromRate;
    }

    protected function resolveRate(Currency $currency, Currency $baseCurrency, ?string $date = null): float
    {
        if ($currency->is_base) {
            return 1;
        }

        if ($date) {
            $rateLog = ExchangeRateLog::where('currency_id', $currency->id)
                ->where('rate_date', '<=', $date)
                ->orderBy('rate_date', 'desc')
                ->first();

            if ($rateLog) {
                return (float) $rateLog->rate;
            }
        }

        return (float) $currency->exchange_rate;
    }

    public function format(float $amount, string $currencyCode): string
    {
        $currency = Currency::where('code', $currencyCode)->first();

        if (!$currency) {
            return number_format($amount, 2, ',', '.');
        }

        return number_format(
            $amount,
            $currency->decimal_places,
            $currency->decimal_separator,
            $currency->thousands_separator
        );
    }

    public function updateExchangeRate(Currency $currency, float $newRate, ?string $date = null): void
    {
        $currency->exchange_rate = $newRate;
        $currency->save();

        ExchangeRateLog::create([
            'currency_id' => $currency->id,
            'rate_date' => $date ?? now()->toDateString(),
            'rate' => $newRate,
            'created_at' => now(),
        ]);
    }
}
