<?php

namespace App\Services\DataSource\Parsers;

use App\Services\DataSource\Parsers\Contracts\DataParserInterface;
use Exception;

class DataParserFactory
{
    /**
     * Registered parsers
     *
     * @var array
     */
    protected array $parsers = [
        'json' => JsonParser::class,
        'xml' => XmlParser::class,
        'csv' => CsvParser::class,
        'rss' => RssParser::class,
        'text' => TextParser::class,
        'txt' => TextParser::class,
    ];

    /**
     * Make a parser instance for the given format
     *
     * @param string $format
     * @return DataParserInterface
     * @throws \Exception
     */
    public function make(string $format): DataParserInterface
    {
        $format = strtolower($format);

        if (!isset($this->parsers[$format])) {
            throw new Exception("Unsupported format: {$format}");
        }

        $parserClass = $this->parsers[$format];
        return new $parserClass();
    }

    /**
     * Register a custom parser
     *
     * @param string $format
     * @param string $parserClass
     * @return void
     */
    public function register(string $format, string $parserClass): void
    {
        $this->parsers[strtolower($format)] = $parserClass;
    }

    /**
     * Get all supported formats
     *
     * @return array
     */
    public function getSupportedFormats(): array
    {
        return array_keys($this->parsers);
    }
}
