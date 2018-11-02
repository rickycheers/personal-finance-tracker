<?php

namespace App\PFT\EmailExtractor\Extractors;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseEmailExtractor implements EmailExtractor
{
    protected $content;

    protected $dom;

    public function __construct($content)
    {
        $this->content = $content;
        $this->dom = new Crawler($content);
    }
}