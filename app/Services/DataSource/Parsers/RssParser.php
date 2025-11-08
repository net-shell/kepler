<?php

declare(strict_types=1);

namespace App\Services\DataSource\Parsers;

use App\Services\DataSource\Parsers\Contracts\DataParserInterface;
use Exception;

class RssParser implements DataParserInterface
{
    /**
     * Parse RSS feed content into an array of items
     *
     * @param string $content
     * @return array
     * @throws \Exception
     */
    public function parse(string $content): array
    {
        try {
            $xml = simplexml_load_string($content);
            if ($xml === false) {
                throw new Exception("Invalid RSS");
            }

            $items = [];
            foreach ($xml->channel->item as $item) {
                $items[] = [
                    'title' => (string) $item->title,
                    'description' => (string) $item->description,
                    'link' => (string) $item->link,
                    'pubDate' => (string) $item->pubDate,
                ];
            }

            return $items;
        } catch (\Exception $e) {
            throw new Exception("RSS parsing failed: {$e->getMessage()}");
        }
    }
}
