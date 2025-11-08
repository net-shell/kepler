<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DataSource;
use App\Services\DataSourceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecacheDataSources implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(DataSourceService $service): void
    {
        Log::info('Starting data sources recache job');

        // Get all sources that need caching
        $sources = DataSource::needsCaching()->get();

        Log::info("Found {$sources->count()} data sources that need caching");

        $successCount = 0;
        $failureCount = 0;

        foreach ($sources as $source) {
            try {
                Log::info("Recaching data source: {$source->name} (ID: {$source->id})");

                $data = $service->fetchData($source, false);

                Log::info("Successfully cached {$source->name}: " . count($data) . " items");
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Failed to cache data source {$source->name} (ID: {$source->id}): {$e->getMessage()}");
                $failureCount++;
            }
        }

        Log::info("Data sources recache job completed. Success: {$successCount}, Failures: {$failureCount}");
    }
}
