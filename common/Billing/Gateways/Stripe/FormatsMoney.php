<?php

namespace Common\Billing\Gateways\Stripe;

use Common\Billing\Models\Price;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Parser\IntlLocalizedDecimalParser;
use NumberFormatter;

trait FormatsMoney
{
    protected function priceToCents(Price $price): string
    {
        $currencies = new ISOCurrencies();
        $numberFormatter = new NumberFormatter('en', NumberFormatter::DECIMAL);
        $moneyParser = new IntlLocalizedDecimalParser(
            $numberFormatter,
            $currencies,
        );
        $money = $moneyParser->parse(
            $price->amount,
            new Currency($price->currency),
        );

        return $money->getAmount();
    }
}
