<?php

namespace App\Console\Commands;

use App\PFT\MailImporter;
use Illuminate\Console\Command;

class ImportEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pft:import-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads transaction notification emails and imports them to be processed';

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
        $reader = new MailImporter($this);
        $reader->run();
    }
}
