<?php

namespace App\Services\DataSource\Parsers\Contracts;

interface DataParserInterface
{
    /**
     * Parse the content into an array of items
     *
     * @param string $content
     * @return array
     * @throws \Exception
     */
    public function parse(string $content): array;
}
