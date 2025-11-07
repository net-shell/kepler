<?php

namespace App\Http\Controllers;

use App\Models\DataSource;
use App\Services\DataSourceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DataSourceController extends Controller
{
    protected DataSourceService $dataSourceService;

    public function __construct(DataSourceService $dataSourceService)
    {
        $this->dataSourceService = $dataSourceService;
    }

    /**
     * Get all data sources
     */
    public function index(): JsonResponse
    {
        $sources = DataSource::orderBy('created_at', 'desc')->get();

        // Add cache status to each source
        $sources->each(function ($source) {
            $source->cache_valid = $source->isCacheValid();
            $source->cache_expires_at = $source->cacheExpiresAt();
        });

        return response()->json([
            'success' => true,
            'sources' => $sources,
        ]);
    }

    /**
     * Get a single data source
     */
    public function show(int $id): JsonResponse
    {
        $source = DataSource::findOrFail($id);
        $source->cache_valid = $source->isCacheValid();
        $source->cache_expires_at = $source->cacheExpiresAt();

        return response()->json([
            'success' => true,
            'source' => $source,
        ]);
    }

    /**
     * Create a new data source
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:database,url,api',
            'config' => 'required|array',
            'cache_ttl' => 'required|integer|min:0',
            'enabled' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $source = DataSource::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data source created successfully',
            'source' => $source,
        ], 201);
    }

    /**
     * Update a data source
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $source = DataSource::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:database,url,api',
            'config' => 'sometimes|required|array',
            'cache_ttl' => 'sometimes|required|integer|min:0',
            'enabled' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $source->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data source updated successfully',
            'source' => $source->fresh(),
        ]);
    }

    /**
     * Delete a data source
     */
    public function destroy(int $id): JsonResponse
    {
        $source = DataSource::findOrFail($id);
        $source->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data source deleted successfully',
        ]);
    }

    /**
     * Test a data source connection
     */
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:database,url,api',
            'config' => 'required|array',
        ]);

        try {
            $result = $this->dataSourceService->testConnection(
                $validated['config'],
                $validated['type']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh cache for a specific data source
     */
    public function refresh(int $id): JsonResponse
    {
        $source = DataSource::findOrFail($id);

        try {
            $data = $this->dataSourceService->fetchData($source, false);

            return response()->json([
                'success' => true,
                'message' => 'Cache refreshed successfully',
                'count' => count($data),
                'cached_at' => $source->fresh()->last_cached_at,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cached data for a source
     */
    public function getData(int $id): JsonResponse
    {
        $source = DataSource::findOrFail($id);

        try {
            $data = $this->dataSourceService->fetchData($source);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data),
                'cached_at' => $source->last_cached_at,
                'cache_valid' => $source->isCacheValid(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get preview of data (first few items)
     */
    public function preview(int $id): JsonResponse
    {
        $source = DataSource::findOrFail($id);

        try {
            $data = $this->dataSourceService->fetchData($source);
            $preview = array_slice($data, 0, 5);

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'total_count' => count($data),
                'cached_at' => $source->last_cached_at,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle enabled status
     */
    public function toggle(int $id): JsonResponse
    {
        $source = DataSource::findOrFail($id);
        $source->update(['enabled' => !$source->enabled]);

        return response()->json([
            'success' => true,
            'message' => $source->enabled ? 'Data source enabled' : 'Data source disabled',
            'enabled' => $source->enabled,
        ]);
    }
}
