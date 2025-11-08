<?php

declare(strict_types=1);

namespace App\Services\DataSource\Parsers;

use App\Services\DataSource\Parsers\Contracts\DataParserInterface;
use Exception;

class XmlParser implements DataParserInterface
{
    /**
     * Parse XML content into an array of items
     *
     * @param string $content
     * @return array
     * @throws \Exception
     */
    public function parse(string $content): array
    {
        try {
            $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml === false) {
                throw new Exception("Invalid XML");
            }

            $json = json_encode($xml);
            $data = json_decode($json, true);

            return is_array($data) ? [$data] : $data;
        } catch (\Exception $e) {
            throw new Exception("XML parsing failed: {$e->getMessage()}");
        }
    }
}
