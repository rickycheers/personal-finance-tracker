<?php

namespace App\Console\Commands;

use App\Models\Payload;
use App\Models\Split;
use App\Models\Transaction;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Money\Parser\IntlMoneyParser;
use Symfony\Component\DomCrawler\Crawler;

class ImportTransactionsFromEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pft:import-from-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extracts transactions from the email notifications and imports them';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subject = "Credit One Balance and Available Credit";

        $payloads = Payload::where('type', 'client_email_notification')
            ->where('data->subject', $subject)
            ->get();

        $this->info("Processing subject: $subject");

        $transactions = $payloads->each(function ($payload) {
            $this->importEmail($payload);
        });

        $this->info(sprintf("Imported %d transactions", count($transactions)));
    }

    protected function importEmail($payload)
    {
        $email = collect(json_decode($payload->data, true));
        $html = base64UrlDecode($email->get('html'));
        $crawler = new Crawler($html);

        $rows = $crawler->filter('tr')->reduce(function (Crawler $node, $i) {
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

            $transaction = $this->store([
                'transactionDate' => Carbon::make($tds->eq(0)->text()),
                'reconciliationDate' => Carbon::make($tds->eq(1)->text()),
                'description' => cleanSpace($tds->eq(2)->text()),
                'amount' => $money,
            ]);

            return $transaction;
        });

        return $transactions;
    }

    protected function store($data)
    {
        DB::beginTransaction();

        $transaction = new Transaction([
            'date' => data_get($data, 'transactionDate'),
            'description' => data_get($data, 'description'),
        ]);

        $split = new Split([
            'amount' => data_get($data, 'amount'),
            'description' => data_get($data, 'description'),
            'reconciliation_date' => data_get($data, 'reconciliationDate'),
        ]);

        $transaction->save();
        $transaction->splits()->save($split);

        DB::commit();
    }
}
