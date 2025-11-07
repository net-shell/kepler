<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DataSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataFeedController extends Controller
{
    protected DataSourceService $dataSourceService;

    public function __construct(DataSourceService $dataSourceService)
    {
        $this->dataSourceService = $dataSourceService;
    }

    /**
     * Get all data from documents and data sources combined
     */
    public function feed(Request $request): JsonResponse
    {
        try {
            $includeDocuments = $request->boolean('include_documents', true);
            $includeDataSources = $request->boolean('include_data_sources', true);
            $useCache = $request->boolean('use_cache', true);

            $allData = [];

            // Include documents from database
            if ($includeDocuments) {
                $documents = Document::all()->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'body' => $doc->body,
                        'tags' => $doc->tags ?? [],
                        'metadata' => $doc->metadata ?? [],
                        '_source_type' => 'document',
                        '_source_name' => 'Database Documents',
                    ];
                })->toArray();

                $allData = array_merge($allData, $documents);
            }

            // Include data from external sources
            if ($includeDataSources) {
                $sourceData = $this->dataSourceService->getAllSourcesData($useCache);
                $allData = array_merge($allData, $sourceData);
            }

            return response()->json([
                'success' => true,
                'count' => count($allData),
                'data' => $allData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data feed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics about the data feed
     */
    public function stats(): JsonResponse
    {
        try {
            $documentCount = Document::count();
            $dataSourceCount = \App\Models\DataSource::enabled()->count();

            // Get total items from data sources
            $sourceItemsCount = 0;
            $sources = \App\Models\DataSource::enabled()->get();

            foreach ($sources as $source) {
                if ($source->cached_data) {
                    $sourceItemsCount += count($source->cached_data);
                }
            }

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_documents' => $documentCount,
                    'total_data_sources' => $dataSourceCount,
                    'total_source_items' => $sourceItemsCount,
                    'total_items' => $documentCount + $sourceItemsCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats: ' . $e->getMessage(),
            ], 500);
        }
    }
}
