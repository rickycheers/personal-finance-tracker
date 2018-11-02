<?php

namespace App\PFT\EmailExtractor\Extractors;

interface EmailExtractor
{
    public function extractTransactions();

    public function extractAccountNumber();
}