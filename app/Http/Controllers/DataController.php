<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\FileProcessingService;
use App\Http\Controllers\DataFeedController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
    public function __construct(
        private FileProcessingService $fileProcessingService,
        private DataFeedController $dataFeedController
    ) {}
    /**
     * Feed data into the system (single document)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'path' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'tags' => 'array',
            'tags.*' => 'string',
            'metadata' => 'array'
        ]);

        // If no path provided, use title as filename in root
        if (empty($validated['path'])) {
            $validated['path'] = '/' . $validated['title'];
        }

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
            'documents.*.path' => 'nullable|string|max:1000',
            'documents.*.body' => 'required|string',
            'documents.*.tags' => 'array',
            'documents.*.metadata' => 'array'
        ]);

        try {
            $documents = [];

            foreach ($request->input('documents') as $docData) {
                // If no path provided, use title as filename in root
                if (empty($docData['path'])) {
                    $docData['path'] = '/' . $docData['title'];
                }
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
        $documents = Document::select('id', 'title', 'path', 'tags', 'created_at', 'updated_at')
            ->latest()
            ->paginate($perPage);

        return response()->json($documents);
    }

    /**
     * Get a single document
     */
    public function show(int $id): JsonResponse
    {
        // Handle negative IDs (data source items)
        if ($id < 0) {
            return $this->showDataSourceItem($id);
        }

        // Handle positive IDs (database documents)
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
     * Show a data source item by its pseudo-ID
     */
    private function showDataSourceItem(int $pseudoId): JsonResponse
    {
        $document = $this->dataFeedController->findDataSourceItemByPseudoId($pseudoId);

        if (!$document) {
            return response()->json([
                'success' => false,
                'error' => 'Data source item not found'
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
            'path' => 'string|max:1000',
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
     * Bulk delete documents
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:documents,id'
        ]);

        try {
            $count = Document::whereIn('id', $validated['ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} document(s) deleted successfully",
                'deleted_count' => $count
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

    /**
     * Get folder tree structure
     */
    public function getFolderTree(Request $request): JsonResponse
    {
        try {
            $documents = Document::select('id', 'title', 'path', 'tags', 'created_at', 'updated_at')
                ->orderBy('path')
                ->get();
            $tree = $this->buildFolderTree($documents);

            return response()->json([
                'success' => true,
                'tree' => $tree
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build hierarchical folder tree from flat document list
     */
    private function buildFolderTree($documents): array
    {
        $folders = [];

        foreach ($documents as $doc) {
            $path = $doc->path ?? '/' . $doc->title;

            // Skip if path is just root
            if ($path === '/') {
                $path = '/' . $doc->title;
            }

            $parts = array_values(array_filter(explode('/', $path)));

            // Build folder structure
            $currentPath = '';
            foreach ($parts as $index => $part) {
                $currentPath .= '/' . $part;
                $isFile = $index === count($parts) - 1;

                if (!isset($folders[$currentPath])) {
                    if ($isFile) {
                        // This is the file itself
                        $folders[$currentPath] = [
                            'path' => $currentPath,
                            'name' => $part,
                            'type' => 'file',
                            'id' => $doc->id,
                            'document' => $doc
                        ];
                    } else {
                        // This is a folder
                        $folders[$currentPath] = [
                            'path' => $currentPath,
                            'name' => $part,
                            'type' => 'folder',
                            'children' => []
                        ];
                    }
                }
            }
        }

        // Build hierarchical structure
        $tree = [];
        $childPaths = [];

        // First pass: identify all child paths
        foreach ($folders as $path => $item) {
            $parentPath = dirname($path);
            if ($parentPath !== '/' && $parentPath !== '.') {
                $childPaths[] = $path;
            }
        }

        // Second pass: build tree
        foreach ($folders as $path => $item) {
            $parentPath = dirname($path);

            if ($parentPath === '/' || $parentPath === '.') {
                // Root level item
                if ($item['type'] === 'folder') {
                    // Add children to folder
                    $children = [];
                    foreach ($folders as $childPath => $childItem) {
                        if (dirname($childPath) === $path) {
                            $children[] = $childItem;
                        }
                    }
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        // Recursively add children to nested folders
        $tree = $this->addChildrenToFolders($tree, $folders);

        return $tree;
    }

    /**
     * Recursively add children to folder nodes
     */
    private function addChildrenToFolders($nodes, $allFolders): array
    {
        $result = [];

        foreach ($nodes as $node) {
            if ($node['type'] === 'folder') {
                $children = [];
                foreach ($allFolders as $path => $item) {
                    if (dirname($path) === $node['path']) {
                        if ($item['type'] === 'folder') {
                            // Recursively add children to this folder
                            $item = $this->addChildrenToFolders([$item], $allFolders)[0];
                        }
                        $children[] = $item;
                    }
                }
                $node['children'] = $children;
            }
            $result[] = $node;
        }

        return $result;
    }
    /**
     * Move document to a new path
     */
    public function moveDocument(Request $request, int $id): JsonResponse
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'error' => 'Document not found'
            ], 404);
        }

        $validated = $request->validate([
            'new_path' => 'required|string|max:1000'
        ]);

        try {
            $document->update(['path' => $validated['new_path']]);

            return response()->json([
                'success' => true,
                'message' => 'Document moved successfully',
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
     * Get documents in a specific folder
     */
    public function getByFolder(Request $request): JsonResponse
    {
        $folder = $request->input('folder', '/');
        $recursive = $request->input('recursive', false);

        try {
            $query = Document::select('id', 'title', 'path', 'tags', 'created_at', 'updated_at');

            if ($recursive) {
                $query->inFolder($folder);
            } else {
                // Only direct children - not implemented via scope, using simple LIKE
                $pattern = $folder === '/' ? '/^\/[^\/]+$/' : '/^' . preg_quote($folder, '/') . '\/[^\/]+$/';
                $query->where('path', 'like', $folder === '/' ? '/%' : $folder . '/%');
            }

            $documents = $query->orderBy('path')->get();

            return response()->json([
                'success' => true,
                'documents' => $documents,
                'folder' => $folder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
