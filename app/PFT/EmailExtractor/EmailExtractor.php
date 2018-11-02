<?php

namespace App\PFT\EmailExtractor;

use App\PFT\EmailExtractor\Extractors\CreditOne;
use App\PFT\EmailExtractor\Extractors\EmailExtractor as EmailExtractorInterface;

class EmailExtractor
{
    const STRATEGIES = [
        'credit_one' => CreditOne::class,
    ];

    protected $extractor;

    public function __construct(EmailExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
    }

    public static function create($type, $content)
    {
        $class = data_get(self::STRATEGIES, $type);

        if (!$class) {
            throw new \Exception("Strategy '$type' not implemented");
        }

        return new static(new $class($content));
    }

    public function extractTransactions()
    {
        return $this->extractor->extractTransactions();
    }

    public function extractAccountNumber()
    {
        return $this->extractor->extractAccountNumber();
    }
}
