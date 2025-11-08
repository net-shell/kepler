<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataSource;
use App\Services\DataSourceService;

class RefreshDataSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-sources:refresh {--all : Refresh all sources, not just expired ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh data from external data sources';

    /**
     * Execute the console command.
     */
    public function handle(DataSourceService $service)
    {
        $this->info('🔄 Refreshing data sources...');
        $this->newLine();

        // Get sources to refresh
        if ($this->option('all')) {
            $sources = DataSource::enabled()->get();
            $this->info('Refreshing all enabled sources...');
        } else {
            $sources = DataSource::needsCaching()->get();
            $this->info('Refreshing sources with expired caches...');
        }

        if ($sources->isEmpty()) {
            $this->info('✓ No sources need refreshing');
            return Command::SUCCESS;
        }

        $this->info("Found {$sources->count()} source(s) to refresh");
        $this->newLine();

        $bar = $this->output->createProgressBar($sources->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;
        $results = [];

        foreach ($sources as $source) {
            try {
                $data = $service->fetchData($source, false);
                $successCount++;
                $results[] = [
                    'name' => $source->name,
                    'status' => '✓',
                    'count' => count($data),
                    'message' => 'Success',
                ];
            } catch (\Exception $e) {
                $failureCount++;
                $results[] = [
                    'name' => $source->name,
                    'status' => '✗',
                    'count' => 0,
                    'message' => $e->getMessage(),
                ];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results table
        $this->table(
            ['Status', 'Source', 'Items', 'Message'],
            array_map(fn($r) => [
                $r['status'],
                $r['name'],
                $r['count'],
                $r['message']
            ], $results)
        );

        $this->newLine();
        $this->info("✓ Completed: {$successCount} successful, {$failureCount} failed");

        return Command::SUCCESS;
    }
}
