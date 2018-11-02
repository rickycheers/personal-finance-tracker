<?php

namespace App\PFT\EmailExtractor\Extractors;

use Carbon\Carbon;
use Money\Parser\IntlMoneyParser;
use Symfony\Component\DomCrawler\Crawler;

class CreditOne extends BaseEmailExtractor
{
    public function extractTransactions()
    {
        $rows = $this->dom->filter('tr')->reduce(function (Crawler $node, $i) {
            return $node->children()->count() === 4;
        });

        /** @var IntlMoneyParser $moneyParser */
        $moneyParser = app()->get('money.parser');

        $transactions = $rows->siblings()->each(function (Crawler $row) use ($moneyParser) {
            $tds = $row->children();
            $amount = $tds->eq(3)->text();
            if (str_contains($amount, '-')) {
                $amount = str_replace('-', '', $amount);
                $money = $moneyParser->parse($amount)->negative();
            } else {
                $money = $moneyParser->parse($amount);
            }

            return [
                'transactionDate' => Carbon::make($tds->eq(0)->text()),
                'reconciliationDate' => Carbon::make($tds->eq(1)->text()),
                'description' => cleanSpace($tds->eq(2)->text()),
                'amount' => $money,
            ];
        });

        return $transactions;
    }

    public function extractAccountNumber()
    {
        $text = cleanSpace(html_entity_decode(strip_tags($this->content)));
        preg_match("/Balance and Available Credit \w+\W+(\d{4})/i", $text, $matches);
        return data_get($matches, 1);
    }
}
