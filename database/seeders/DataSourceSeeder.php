<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DataSource;

class DataSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data sources
        DataSource::truncate();

        // Example 1: Public JSON API (JSONPlaceholder)
        DataSource::create([
            'name' => 'JSONPlaceholder Posts',
            'type' => 'api',
            'description' => 'Sample blog posts from JSONPlaceholder API',
            'cache_ttl' => 3600, // 1 hour
            'enabled' => true,
            'config' => [
                'url' => 'https://jsonplaceholder.typicode.com/posts',
                'method' => 'get',
                'auth_type' => 'none',
                'format' => 'json',
            ],
        ]);

        // Example 2: Hacker News RSS Feed
        DataSource::create([
            'name' => 'Hacker News Feed',
            'type' => 'url',
            'description' => 'Latest stories from Hacker News',
            'cache_ttl' => 1800, // 30 minutes
            'enabled' => true,
            'config' => [
                'url' => 'https://news.ycombinator.com/rss',
                'format' => 'rss',
            ],
        ]);

        // Example 3: Database Query (existing documents)
        DataSource::create([
            'name' => 'Recent Documents',
            'type' => 'database',
            'description' => 'Recently created documents from our database',
            'cache_ttl' => 600, // 10 minutes
            'enabled' => true,
            'config' => [
                'query' => 'SELECT id, title, body, tags, metadata FROM documents ORDER BY created_at DESC LIMIT 50',
                'connection' => 'sqlite',
            ],
        ]);

        // Example 4: GitHub API (disabled by default - requires token)
        DataSource::create([
            'name' => 'GitHub Trending Repos',
            'type' => 'api',
            'description' => 'Trending repositories from GitHub (requires API token)',
            'cache_ttl' => 7200, // 2 hours
            'enabled' => false, // Disabled until token is configured
            'config' => [
                'url' => 'https://api.github.com/search/repositories?q=stars:>1000&sort=stars&order=desc',
                'method' => 'get',
                'auth_type' => 'bearer',
                'token' => '', // User needs to add their GitHub token
                'format' => 'json',
                'data_path' => 'items',
            ],
        ]);

        // Example 5: Simple text file
        DataSource::create([
            'name' => 'Sample Text Data',
            'type' => 'url',
            'description' => 'Example of fetching plain text data',
            'cache_ttl' => 86400, // 24 hours
            'enabled' => false, // Disabled - example only
            'config' => [
                'url' => 'https://raw.githubusercontent.com/dwyl/english-words/master/words_alpha.txt',
                'format' => 'text',
            ],
        ]);

        $this->command->info('✓ Created 5 sample data sources');
        $this->command->info('');
        $this->command->info('Active sources:');
        $this->command->info('  - JSONPlaceholder Posts (API)');
        $this->command->info('  - Hacker News Feed (RSS)');
        $this->command->info('  - Recent Documents (Database)');
        $this->command->info('');
        $this->command->info('Disabled sources (need configuration):');
        $this->command->info('  - GitHub Trending Repos (needs API token)');
        $this->command->info('  - Sample Text Data (example only)');
    }
}
