<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Parser\IntlMoneyParser;
use NumberFormatter;

class MoneyServivceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $numberFormatter = new NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $currencies = new ISOCurrencies();

        $this->app->singleton('money.parser', function () use ($numberFormatter, $currencies) {
            return new IntlMoneyParser($numberFormatter, $currencies);
        });

        $this->app->singleton('money.formatter', function () use ($numberFormatter, $currencies){
            return new IntlMoneyFormatter($numberFormatter, $currencies);
        });
    }
}
