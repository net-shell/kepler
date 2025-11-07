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
                        'path' => $doc->path,
                        'body' => $doc->body,
                        'tags' => $doc->tags ?? [],
                        'metadata' => array_merge($doc->metadata ?? [], [
                            'source_type' => 'database',
                            'source_name' => 'Database Documents',
                            'is_imported' => false,
                        ]),
                        'created_at' => $doc->created_at?->toIso8601String(),
                        'updated_at' => $doc->updated_at?->toIso8601String(),
                    ];
                })->toArray();

                $allData = array_merge($allData, $documents);
            }

            // Include data from external sources
            if ($includeDataSources) {
                $sourceData = $this->dataSourceService->getAllSourcesData($useCache);

                // Transform source data to Document-like structure
                $transformedSourceData = array_map(function ($item) {
                    return $this->transformSourceItemToDocument($item);
                }, $sourceData);

                $allData = array_merge($allData, $transformedSourceData);
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
     * Transform a data source item to Document model structure
     *
     * @param array $item
     * @return array
     */
    protected function transformSourceItemToDocument(array $item): array
    {
        // Extract source metadata
        $sourceId = $item['_source_id'] ?? null;
        $sourceName = $item['_source_name'] ?? 'Unknown Source';
        $sourceType = $item['_source_type'] ?? 'unknown';
        $pseudoId = $item['_pseudo_id'] ?? null;
        $path = $item['_path'] ?? null;

        // Remove internal metadata from item
        $cleanItem = $item;
        unset($cleanItem['_source_id'], $cleanItem['_source_name'], $cleanItem['_source_type'], $cleanItem['_pseudo_id'], $cleanItem['_path']);

        // Determine title (try common field names)
        $title = $cleanItem['title']
            ?? $cleanItem['name']
            ?? $cleanItem['label']
            ?? $cleanItem['heading']
            ?? $cleanItem['subject']
            ?? 'Untitled';

        // Determine body/content (try common field names)
        $body = $cleanItem['body']
            ?? $cleanItem['content']
            ?? $cleanItem['description']
            ?? $cleanItem['text']
            ?? $cleanItem['message']
            ?? json_encode($cleanItem);

        // Use path from service or generate a new one
        if (!$path) {
            $itemId = $cleanItem['id'] ?? $cleanItem['identifier'] ?? uniqid();
            $path = "/imports/{$sourceType}/{$sourceName}/{$itemId}";
        }

        // Use pseudo-ID from service or generate a new one
        if (!$pseudoId) {
            $pseudoId = -abs(crc32($path));
        }

        // Extract or create tags
        $tags = [];
        if (isset($cleanItem['tags']) && is_array($cleanItem['tags'])) {
            $tags = $cleanItem['tags'];
        } elseif (isset($cleanItem['categories']) && is_array($cleanItem['categories'])) {
            $tags = $cleanItem['categories'];
        }

        // Build metadata
        $metadata = [
            'source_type' => 'data_source',
            'source_name' => $sourceName,
            'source_id' => $sourceId,
            'data_source_type' => $sourceType,
            'is_imported' => true,
            'imported_at' => now()->toIso8601String(),
            'original_data' => $cleanItem, // Keep original data for reference
        ];

        return [
            'id' => $pseudoId,
            'title' => $title,
            'path' => $path,
            'body' => $body,
            'tags' => $tags,
            'metadata' => $metadata,
            'created_at' => $cleanItem['created_at'] ?? $cleanItem['date'] ?? now()->toIso8601String(),
            'updated_at' => $cleanItem['updated_at'] ?? $cleanItem['modified'] ?? now()->toIso8601String(),
        ];
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

    /**
     * Find a data source item by its pseudo-ID
     * Returns the transformed document structure or null if not found
     */
    public function findDataSourceItemByPseudoId(int $pseudoId): ?array
    {
        try {
            $allSourceData = $this->dataSourceService->getAllSourcesData(true);

            foreach ($allSourceData as $item) {
                $transformed = $this->transformSourceItemToDocument($item);

                // Calculate pseudo-ID
                $calculatedPseudoId = -abs(crc32($transformed['path']));

                if ($calculatedPseudoId === $pseudoId) {
                    return array_merge($transformed, ['id' => $pseudoId]);
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
