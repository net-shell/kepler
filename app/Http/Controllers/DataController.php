<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DataController extends Controller
{
    /**
     * Feed data into the system (single document)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'array',
            'tags.*' => 'string',
            'metadata' => 'array'
        ]);

        try {
            $document = Document::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Document created successfully',
                'document' => $document
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch feed data (multiple documents)
     */
    public function batchStore(Request $request): JsonResponse
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*.title' => 'required|string|max:255',
            'documents.*.body' => 'required|string',
            'documents.*.tags' => 'array',
            'documents.*.metadata' => 'array'
        ]);

        try {
            $documents = [];

            foreach ($request->input('documents') as $docData) {
                $documents[] = Document::create($docData);
            }

            return response()->json([
                'success' => true,
                'message' => count($documents) . ' documents created successfully',
                'documents' => $documents
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all documents
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $documents = Document::latest()->paginate($perPage);

        return response()->json($documents);
    }

    /**
     * Get a single document
     */
    public function show(int $id): JsonResponse
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'error' => 'Document not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'document' => $document
        ]);
    }

    /**
     * Update a document
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'error' => 'Document not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'body' => 'string',
            'tags' => 'array',
            'tags.*' => 'string',
            'metadata' => 'array'
        ]);

        try {
            $document->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'document' => $document
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a document
     */
    public function destroy(int $id): JsonResponse
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'error' => 'Document not found'
            ], 404);
        }

        try {
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk upload documents from file (CSV, TXT, Excel, PDF, JSON, TSV)
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls,pdf,json,tsv|max:10240' // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $documents = [];

            switch (strtolower($extension)) {
                case 'csv':
                case 'tsv':
                    $documents = $this->parseCSV($file, $extension === 'tsv' ? "\t" : ',');
                    break;

                case 'xlsx':
                case 'xls':
                    $documents = $this->parseExcel($file);
                    break;

                case 'pdf':
                    $documents = $this->parsePDF($file);
                    break;

                case 'txt':
                    $documents = $this->parseText($file);
                    break;

                case 'json':
                    $documents = $this->parseJSON($file);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Unsupported file format'
                    ], 400);
            }

            if (empty($documents)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid documents found in file'
                ], 400);
            }

            // Create documents in database
            $created = [];
            foreach ($documents as $docData) {
                $created[] = Document::create($docData);
            }

            return response()->json([
                'success' => true,
                'message' => count($created) . ' documents uploaded successfully',
                'count' => count($created)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse CSV/TSV file
     */
    private function parseCSV($file, string $delimiter = ','): array
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
    private function parseExcel($file): array
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
    private function parsePDF($file): array
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
    private function parseText($file): array
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
    private function parseJSON($file): array
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
