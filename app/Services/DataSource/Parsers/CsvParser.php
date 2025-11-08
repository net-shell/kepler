<?php

declare(strict_types=1);

namespace App\Services\DataSource\Parsers;

use App\Services\DataSource\Parsers\Contracts\DataParserInterface;
use Exception;

class CsvParser implements DataParserInterface
{
    /**
     * Parse CSV content into an array of items
     *
     * @param string $content
     * @return array
     * @throws \Exception
     */
    public function parse(string $content): array
    {
        $lines = explode("\n", trim($content));
        if (empty($lines)) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        $data = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $values = str_getcsv($line);
            $row = [];

            foreach ($headers as $i => $header) {
                $row[$header] = $values[$i] ?? null;
            }

            $data[] = $row;
        }

        return $data;
    }
}
