<?php

namespace App\Services\DataSource\Parsers;

use App\Services\DataSource\Parsers\Contracts\DataParserInterface;

class TextParser implements DataParserInterface
{
    /**
     * Parse plain text content into an array of items (one per line)
     *
     * @param string $content
     * @return array
     */
    public function parse(string $content): array
    {
        $lines = explode("\n", trim($content));
        return array_map(
            fn($line) => ['text' => trim($line)],
            array_filter($lines, fn($line) => !empty(trim($line)))
        );
    }
}
