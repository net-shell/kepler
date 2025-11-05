<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileProcessingService
{
    /**
     * Process uploaded file and extract documents
     */
    public function processFile(UploadedFile $file): array
    {
        $extension = $file->getClientOriginalExtension();

        return match (strtolower($extension)) {
            'csv' => $this->parseCSV($file, ','),
            'tsv' => $this->parseCSV($file, "\t"),
            'xlsx', 'xls' => $this->parseExcel($file),
            'pdf' => $this->parsePDF($file),
            'txt' => $this->parseText($file),
            'json' => $this->parseJSON($file),
            default => throw new \Exception('Unsupported file format'),
        };
    }

    /**
     * Parse CSV/TSV file
     */
    private function parseCSV(UploadedFile $file, string $delimiter = ','): array
    {
        $documents = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new \Exception('Could not open file');
        }

        // Read header
        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            fclose($handle);
            throw new \Exception('Invalid CSV format');
        }

        // Normalize headers
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);

        // Check required columns
        if (!in_array('title', $header) || !in_array('body', $header)) {
            fclose($handle);
            throw new \Exception('CSV must contain "title" and "body" columns');
        }

        // Read rows
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) !== count($header)) {
                continue; // Skip malformed rows
            }

            $data = array_combine($header, $row);

            $document = [
                'title' => trim($data['title']),
                'body' => trim($data['body']),
            ];

            // Optional fields
            if (isset($data['tags']) && !empty($data['tags'])) {
                $document['tags'] = array_map('trim', explode(',', $data['tags']));
            }

            if (isset($data['metadata']) && !empty($data['metadata'])) {
                $document['metadata'] = json_decode($data['metadata'], true) ?? [];
            }

            if (!empty($document['title']) && !empty($document['body'])) {
                $documents[] = $document;
            }
        }

        fclose($handle);
        return $documents;
    }

    /**
     * Parse Excel file
     */
    private function parseExcel(UploadedFile $file): array
    {
        // Check if PhpSpreadsheet is available
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new \Exception('Excel parsing requires PhpSpreadsheet package. Please install it with: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            throw new \Exception('Excel file is empty');
        }

        // First row is header
        $header = array_map('trim', array_shift($rows));
        $header = array_map('strtolower', $header);

        // Check required columns
        if (!in_array('title', $header) || !in_array('body', $header)) {
            throw new \Exception('Excel must contain "title" and "body" columns');
        }

        $documents = [];
        foreach ($rows as $row) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            $document = [
                'title' => trim($data['title'] ?? ''),
                'body' => trim($data['body'] ?? ''),
            ];

            if (isset($data['tags']) && !empty($data['tags'])) {
                $document['tags'] = array_map('trim', explode(',', $data['tags']));
            }

            if (isset($data['metadata']) && !empty($data['metadata'])) {
                $document['metadata'] = json_decode($data['metadata'], true) ?? [];
            }

            if (!empty($document['title']) && !empty($document['body'])) {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    /**
     * Parse PDF file
     */
    private function parsePDF(UploadedFile $file): array
    {
        // Check if Smalot PdfParser is available
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            throw new \Exception('PDF parsing requires smalot/pdfparser package. Please install it with: composer require smalot/pdfparser');
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($file->getRealPath());
        $text = $pdf->getText();

        if (empty(trim($text))) {
            throw new \Exception('Could not extract text from PDF');
        }

        return [[
            'title' => $file->getClientOriginalName(),
            'body' => trim($text),
            'tags' => ['pdf', 'imported'],
            'metadata' => [
                'source_file' => $file->getClientOriginalName(),
                'file_type' => 'pdf'
            ]
        ]];
    }

    /**
     * Parse plain text file
     */
    private function parseText(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false || empty(trim($content))) {
            throw new \Exception('Could not read text file or file is empty');
        }

        return [[
            'title' => $file->getClientOriginalName(),
            'body' => trim($content),
            'tags' => ['text', 'imported'],
            'metadata' => [
                'source_file' => $file->getClientOriginalName(),
                'file_type' => 'txt'
            ]
        ]];
    }

    /**
     * Parse JSON file
     */
    private function parseJSON(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            throw new \Exception('Could not read JSON file');
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        // Ensure it's an array
        if (!is_array($data)) {
            throw new \Exception('JSON must contain an array of documents');
        }

        // If it's a single document object, wrap it in an array
        if (isset($data['title']) && isset($data['body'])) {
            $data = [$data];
        }

        $documents = [];
        foreach ($data as $item) {
            if (!isset($item['title']) || !isset($item['body'])) {
                continue; // Skip items without required fields
            }

            $document = [
                'title' => trim($item['title']),
                'body' => trim($item['body']),
            ];

            if (isset($item['tags']) && is_array($item['tags'])) {
                $document['tags'] = $item['tags'];
            }

            if (isset($item['metadata']) && is_array($item['metadata'])) {
                $document['metadata'] = $item['metadata'];
            }

            if (!empty($document['title']) && !empty($document['body'])) {
                $documents[] = $document;
            }
        }

        return $documents;
    }
}
