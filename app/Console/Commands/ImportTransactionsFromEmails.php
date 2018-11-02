<?php

namespace App\Console\Commands;

use App\Models\Payload;
use App\Models\Split;
use App\Models\Transaction;
use App\PFT\EmailExtractor\EmailExtractor;
use DB;
use Illuminate\Console\Command;

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
        $notifications = collect(config('pft.notifications'));

        $notifications->each(function ($notification) {
            $this->process($notification);
        });
    }

    protected function process($notification)
    {
        $payloads = Payload::where('type', 'client_email_notification')
            ->where('data->subject', $notification['emailSubject'])
            ->get();

        $this->info("Processing: {$notification['bank']} - {$notification['emailSubject']}");

        $total = 0;

        foreach ($payloads as $payload) {
            $email = collect(json_decode($payload->data, true));
            $html = base64UrlDecode($email->get('html'));
            $extractor = EmailExtractor::create($notification['name'], $html);
            $transactions = $extractor->extractTransactions();
            $accountNumber = $extractor->extractAccountNumber();
            $this->importTransactions($transactions, $accountNumber);
            $total += count($transactions);
        }

        $this->info(sprintf("Imported %d transactions", $total));
    }

    protected function importTransactions(array $transactions, $accountNumber)
    {
        foreach ($transactions as $transaction) {
//            $this->store($transaction);
        }
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
