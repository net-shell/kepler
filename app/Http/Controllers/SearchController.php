<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Search documents using the Python AI search script
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'limit' => 'integer|min:1|max:100'
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 5);

        try {
            // Get all documents from database
            $documents = Document::all();
            
            if ($documents->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'results' => [],
                    'message' => 'No documents in database'
                ]);
            }

            // Prepare data for Python script
            $data = $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'body' => $doc->body,
                    'tags' => $doc->tags ?? [],
                    'metadata' => $doc->metadata ?? []
                ];
            })->toArray();

            // Call Python script
            $results = $this->callPythonSearch($data, $query, $limit);

            return response()->json([
                'success' => true,
                'results' => $results,
                'query' => $query
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
        $scriptPath = base_path('scripts/ai_search_api.py');
        
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

        $process = proc_open(
            'python3 ' . escapeshellarg($scriptPath),
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
