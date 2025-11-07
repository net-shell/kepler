<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DataSourceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    protected DataSourceService $dataSourceService;

    public function __construct(DataSourceService $dataSourceService)
    {
        $this->dataSourceService = $dataSourceService;
    }

    /**
     * Search documents using the Python AI search script
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'limit' => 'integer|min:1|max:100',
            'include_documents' => 'boolean',
            'include_data_sources' => 'boolean',
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 5);
        $includeDocuments = $request->boolean('include_documents', true);
        $includeDataSources = $request->boolean('include_data_sources', true);

        try {
            $allData = [];

            // Get documents from database
            if ($includeDocuments) {
                $documents = Document::all();

                $docData = $documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'body' => $doc->body,
                        'tags' => $doc->tags ?? [],
                        'metadata' => $doc->metadata ?? [],
                        '_source_type' => 'document',
                    ];
                })->toArray();

                $allData = array_merge($allData, $docData);
            }

            // Get data from external sources
            if ($includeDataSources) {
                $sourceData = $this->dataSourceService->getAllSourcesData(true);
                $allData = array_merge($allData, $sourceData);
            }

            if (empty($allData)) {
                return response()->json([
                    'success' => true,
                    'results' => [],
                    'message' => 'No data available'
                ]);
            }

            // Call Python script
            $results = $this->callPythonSearch($allData, $query, $limit);

            // Remove body from results to reduce payload size
            $resultsWithoutBody = array_map(function ($result) {
                if (isset($result['record']['body'])) {
                    unset($result['record']['body']);
                }
                return $result;
            }, $results);

            return response()->json([
                'success' => true,
                'results' => $resultsWithoutBody,
                'query' => $query,
                'total_items_searched' => count($allData),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Call the Python AI search script
     */
    private function callPythonSearch(array $data, string $query, int $limit): array
    {
        $scriptPath = base_path(config('aisearch.script_path'));

        // Prepare input data
        $input = json_encode([
            'data' => $data,
            'query' => $query,
            'limit' => $limit
        ]);

        // Execute Python script
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];

        // Determine Python executable path
        $venvPath = base_path(config('aisearch.venv_path'));
        $pythonCmd = $venvPath
            ? escapeshellarg($venvPath . '/bin/python3')
            : 'python3';

        $process = proc_open(
            $pythonCmd . ' ' . escapeshellarg($scriptPath),
            $descriptorspec,
            $pipes
        );

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start Python process');
        }

        // Send input data
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        // Read output
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            throw new \RuntimeException('Python script error: ' . $error);
        }

        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON from Python script: ' . $output);
        }

        return $result;
    }

    /**
     * Get search statistics
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_documents' => Document::count(),
            'latest_document' => Document::latest()->first(),
            'oldest_document' => Document::oldest()->first()
        ]);
    }
}
