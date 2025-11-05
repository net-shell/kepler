<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\FileProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DataController extends Controller
{
    public function __construct(
        private FileProcessingService $fileProcessingService
    ) {}
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
     * Bulk upload documents from file(s) (CSV, TXT, Excel, PDF, JSON, TSV)
     * Supports both single and multiple file uploads
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required_without:files|file|mimes:csv,txt,xlsx,xls,pdf,json,tsv|max:10240', // 10MB max per file
            'files' => 'required_without:file|array',
            'files.*' => 'file|mimes:csv,txt,xlsx,xls,pdf,json,tsv|max:10240' // 10MB max per file
        ]);

        try {
            $files = [];

            // Support both single file and multiple files
            if ($request->hasFile('file')) {
                $files[] = $request->file('file');
            }

            if ($request->hasFile('files')) {
                $files = array_merge($files, $request->file('files'));
            }

            if (empty($files)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No files provided'
                ], 400);
            }

            $allDocuments = [];
            $fileResults = [];

            // Process each file
            foreach ($files as $file) {
                try {
                    $documents = $this->fileProcessingService->processFile($file);
                    $allDocuments = array_merge($allDocuments, $documents);

                    $fileResults[] = [
                        'filename' => $file->getClientOriginalName(),
                        'status' => 'success',
                        'documents_found' => count($documents)
                    ];
                } catch (\Exception $e) {
                    $fileResults[] = [
                        'filename' => $file->getClientOriginalName(),
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            if (empty($allDocuments)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid documents found in any file',
                    'file_results' => $fileResults
                ], 400);
            }

            // Create documents in database
            $created = [];
            foreach ($allDocuments as $docData) {
                $created[] = Document::create($docData);
            }

            return response()->json([
                'success' => true,
                'message' => count($created) . ' documents uploaded successfully from ' . count($files) . ' file(s)',
                'count' => count($created),
                'files_processed' => count($files),
                'file_results' => $fileResults
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process files: ' . $e->getMessage()
            ], 500);
        }
    }
}
