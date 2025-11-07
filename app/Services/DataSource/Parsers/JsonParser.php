<?php

namespace App\Services\DataSource\Parsers;

use App\Services\DataSource\Parsers\Contracts\DataParserInterface;
use Exception;

class JsonParser implements DataParserInterface
{
    /**
     * Parse JSON content into an array of items
     *
     * @param string $content
     * @return array
     * @throws \Exception
     */
    public function parse(string $content): array
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }

        // If data is not an array of items, wrap it
        if (!isset($data[0]) || !is_array($data[0])) {
            return [$data];
        }

        return $data;
    }
}
