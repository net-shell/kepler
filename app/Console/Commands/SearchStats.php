<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;

class SearchStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display search system statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('AI Search System Statistics');
        $this->info('===========================');
        $this->newLine();

        // Total documents
        $total = Document::count();
        $this->line("📊 Total Documents: <fg=green>{$total}</>");

        // Documents by tags
        $documentsWithTags = Document::whereNotNull('tags')->count();
        $this->line("🏷️  Documents with Tags: <fg=green>{$documentsWithTags}</>");

        // Recent documents
        $recent = Document::where('created_at', '>=', now()->subDays(7))->count();
        $this->line("🆕 Added Last 7 Days: <fg=green>{$recent}</>");

        // Latest document
        $latest = Document::latest()->first();
        if ($latest) {
            $this->newLine();
            $this->line("📄 Latest Document:");
            $this->line("   Title: <fg=cyan>{$latest->title}</>");
            $this->line("   Created: <fg=yellow>{$latest->created_at->diffForHumans()}</>");
        }

        // All unique tags
        $allTags = [];
        Document::whereNotNull('tags')->each(function ($doc) use (&$allTags) {
            if (is_array($doc->tags)) {
                $allTags = array_merge($allTags, $doc->tags);
            }
        });
        $uniqueTags = array_unique($allTags);
        
        if (count($uniqueTags) > 0) {
            $this->newLine();
            $this->line("🏷️  Total Unique Tags: <fg=green>" . count($uniqueTags) . "</>");
            $this->line("   Top Tags: <fg=cyan>" . implode(', ', array_slice($uniqueTags, 0, 10)) . "</>");
        }

        $this->newLine();
        return Command::SUCCESS;
    }
}
