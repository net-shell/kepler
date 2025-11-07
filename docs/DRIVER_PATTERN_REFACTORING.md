# Driver/Adapter Pattern Refactoring

## Overview

The DataSourceService has been refactored from a monolithic service (400+ lines) to use the **Driver/Adapter pattern** for better maintainability, extensibility, and separation of concerns.

## Architecture

### Directory Structure

```
app/Services/DataSource/
├── Contracts/
│   └── DataSourceDriverInterface.php     # Driver contract
├── Drivers/
│   ├── AbstractDriver.php                # Base driver class
│   ├── DatabaseDriver.php                # Database source implementation
│   ├── UrlDriver.php                     # URL source implementation
│   └── ApiDriver.php                     # API source implementation
├── Parsers/
│   ├── Contracts/
│   │   └── DataParserInterface.php       # Parser contract
│   ├── JsonParser.php                    # JSON format parser
│   ├── XmlParser.php                     # XML format parser
│   ├── CsvParser.php                     # CSV format parser
│   ├── RssParser.php                     # RSS feed parser
│   ├── TextParser.php                    # Plain text parser
│   └── DataParserFactory.php             # Parser factory
└── DriverManager.php                     # Driver registry and factory
```

### Key Components

#### 1. Driver Interface (`DataSourceDriverInterface`)

Defines the contract for all data source drivers:

```php
interface DataSourceDriverInterface
{
    public function fetch(DataSource $source): array;
    public function validateConfig(array $config): bool;
    public function testConnection(array $config): array;
}
```

#### 2. Abstract Driver (`AbstractDriver`)

Provides shared functionality for all drivers:

- Connection testing logic
- Data normalization
- Common helper methods

#### 3. Concrete Drivers

**DatabaseDriver**
- Executes SQL queries on configured database connections
- Handles multiple database types (MySQL, PostgreSQL, SQLite, etc.)
- Validates query configuration

**UrlDriver**
- Fetches data from HTTP/HTTPS URLs
- Auto-detects content format (JSON, XML, CSV, RSS, text)
- Uses DataParserFactory for format parsing
- Configurable timeout settings

**ApiDriver**
- Handles RESTful API endpoints
- Supports authentication methods:
  - None
  - Bearer Token
  - API Key
  - Basic Auth
  - OAuth2
- Custom headers support
- Nested data extraction via `data_path` config
- Uses DataParserFactory for response parsing

#### 4. Parser System

**DataParserInterface**
- Contract for all format parsers
- Single `parse()` method

**DataParserFactory**
- Registry pattern for parser management
- Maps formats to parser instances
- Extensible via `register()` method

**Parsers**
- **JsonParser**: Handles JSON data with validation
- **XmlParser**: Parses XML using SimpleXML
- **CsvParser**: Parses CSV with header detection
- **RssParser**: Extracts RSS feed items
- **TextParser**: Line-by-line text parsing

#### 5. Driver Manager

Central registry and factory for drivers:

```php
class DriverManager
{
    public function driver(string $type): DataSourceDriverInterface;
    public function register(string $type, DataSourceDriverInterface $driver): void;
    public function getAvailableTypes(): array;
}
```

Default registrations:
- `database` → DatabaseDriver
- `url` → UrlDriver
- `api` → ApiDriver

## Refactored DataSourceService

### Before (Monolithic)

```php
class DataSourceService
{
    public function fetchData(DataSource $source): array { /* 20+ lines */ }
    private function fetchFromDatabase(DataSource $source): array { /* 15+ lines */ }
    private function fetchFromUrl(DataSource $source): array { /* 20+ lines */ }
    private function fetchFromApi(DataSource $source): array { /* 30+ lines */ }
    private function addAuthentication($http, string $authType, array $config) { /* 15+ lines */ }
    private function parseData(string $content, string $format): array { /* 5+ lines */ }
    private function parseJson(string $content): array { /* 10+ lines */ }
    private function parseXml(string $content): array { /* 10+ lines */ }
    private function parseCsv(string $content): array { /* 20+ lines */ }
    private function parseRss(string $content): array { /* 15+ lines */ }
    private function parseText(string $content): array { /* 5+ lines */ }
    private function detectFormat(?string $contentType, string $url): string { /* 15+ lines */ }
    private function extractDataPath(array $data, string $path): array { /* 20+ lines */ }
    // ... more methods
}
```

**Issues:**
- Single class responsibility violation (handles 3 source types + 5 formats)
- Hard to extend (need to modify class for new sources/formats)
- Difficult to test individual components
- Mixed concerns (fetching, parsing, caching, authentication)

### After (Driver Pattern)

```php
class DataSourceService
{
    private DriverManager $driverManager;

    public function __construct(DriverManager $driverManager)
    {
        $this->driverManager = $driverManager;
    }

    public function fetchData(DataSource $source, bool $useCache = true): array
    {
        if ($useCache && $source->isCacheValid()) {
            return $source->cached_data ?? [];
        }

        // Delegate to appropriate driver
        $driver = $this->driverManager->driver($source->type);
        $data = $driver->fetch($source);

        $source->update([
            'cached_data' => $data,
            'last_cached_at' => now(),
        ]);

        return $data;
    }

    public function testConnection(array $config, string $type): array
    {
        try {
            $driver = $this->driverManager->driver($type);
            return $driver->testConnection($config);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAllSourcesData(bool $useCache = true): array
    {
        $sources = DataSource::enabled()->get();
        $allData = [];

        foreach ($sources as $source) {
            try {
                $data = $this->fetchData($source, $useCache);
                
                foreach ($data as $item) {
                    $allData[] = array_merge($item, [
                        '_source_id' => $source->id,
                        '_source_name' => $source->name,
                        '_source_type' => $source->type,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to fetch data from source {$source->id}: {$e->getMessage()}");
            }
        }

        return $allData;
    }

    public function getDriverManager(): DriverManager
    {
        return $this->driverManager;
    }
}
```

**Benefits:**
- Single responsibility (orchestration only)
- Open/closed principle (extend via new drivers)
- Easy to test (mock driver manager)
- Clean separation of concerns
- 116 lines vs 400+ lines

## Extension Guide

### Adding a New Source Type

1. **Create Driver**:

```php
namespace App\Services\DataSource\Drivers;

use App\Models\DataSource;

class GraphQLDriver extends AbstractDriver
{
    public function fetch(DataSource $source): array
    {
        $config = $source->config;
        
        // GraphQL-specific logic
        $response = Http::post($config['endpoint'], [
            'query' => $config['query'],
            'variables' => $config['variables'] ?? [],
        ]);
        
        $data = $response->json()['data'] ?? [];
        return $this->normalizeData($data);
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['endpoint']) && !empty($config['query']);
    }
}
```

2. **Register Driver**:

```php
// In AppServiceProvider or custom service provider
public function boot()
{
    app(DriverManager::class)->register('graphql', new GraphQLDriver());
}
```

3. **Add Constant** (optional):

```php
// In DataSource model
const TYPE_GRAPHQL = 'graphql';
```

### Adding a New Format Parser

1. **Create Parser**:

```php
namespace App\Services\DataSource\Parsers;

class YamlParser implements Contracts\DataParserInterface
{
    public function parse(string $content): array
    {
        $data = yaml_parse($content);
        
        if ($data === false) {
            throw new \Exception('Invalid YAML');
        }
        
        return is_array($data) ? $data : [$data];
    }
}
```

2. **Register Parser**:

```php
// In AppServiceProvider
public function boot()
{
    app(DataParserFactory::class)->register('yaml', new YamlParser());
    app(DataParserFactory::class)->register('yml', new YamlParser());
}
```

## Testing

### Unit Tests

```php
// Test individual drivers
public function test_database_driver_fetches_data()
{
    $driver = new DatabaseDriver();
    $source = DataSource::factory()->make([
        'type' => 'database',
        'config' => ['query' => 'SELECT * FROM users LIMIT 10'],
    ]);
    
    $data = $driver->fetch($source);
    $this->assertIsArray($data);
}

// Test driver manager
public function test_driver_manager_returns_correct_driver()
{
    $manager = new DriverManager();
    $driver = $manager->driver('database');
    
    $this->assertInstanceOf(DatabaseDriver::class, $driver);
}

// Test service with mock driver
public function test_service_uses_driver_manager()
{
    $mockManager = Mockery::mock(DriverManager::class);
    $mockDriver = Mockery::mock(DataSourceDriverInterface::class);
    
    $mockManager->shouldReceive('driver')
        ->once()
        ->with('database')
        ->andReturn($mockDriver);
    
    $mockDriver->shouldReceive('fetch')
        ->once()
        ->andReturn(['test' => 'data']);
    
    $service = new DataSourceService($mockManager);
    $source = DataSource::factory()->make(['type' => 'database']);
    
    $data = $service->fetchData($source, false);
    $this->assertEquals(['test' => 'data'], $data);
}
```

### Integration Tests

```bash
# Test all data sources
php artisan data-sources:refresh --all

# Test specific source
php artisan data-sources:refresh 1

# Test in Tinker
php artisan tinker
>>> $service = app(\App\Services\DataSourceService::class);
>>> $source = \App\Models\DataSource::first();
>>> $data = $service->fetchData($source);
>>> count($data);
```

## Performance Considerations

1. **Lazy Loading**: Drivers are instantiated only when needed
2. **Singleton**: DriverManager and parsers use singleton pattern
3. **Caching**: Original caching strategy preserved
4. **Memory**: Large data sets handled efficiently by parsers

## Migration Notes

### Backward Compatibility

✅ **100% Compatible** - No API changes to:
- DataSourceController
- DataFeedController
- RecacheDataSources job
- RefreshDataSources command
- Frontend Vue components

### Database Schema

✅ **No Changes Required** - Same table structure

### Configuration

✅ **No Changes Required** - Same config files

## Verification

The refactoring was tested and verified:

```bash
$ php artisan data-sources:refresh --all
🔄 Refreshing data sources...
Found 3 source(s) to refresh

+--------+-----------------------+-------+---------+
| Status | Source                | Items | Message |
+--------+-----------------------+-------+---------+
| ✓      | JSONPlaceholder Posts | 100   | Success |
| ✓      | Hacker News Feed      | 30    | Success |
| ✓      | Recent Documents      | 3     | Success |
+--------+-----------------------+-------+---------+

✓ Completed: 3 successful, 0 failed
```

Total items across all sources: **133 items**

## Design Patterns Used

1. **Strategy Pattern**: Different drivers for different source types
2. **Factory Pattern**: DriverManager and DataParserFactory
3. **Adapter Pattern**: Drivers adapt different data sources to common interface
4. **Registry Pattern**: Registration of drivers and parsers
5. **Dependency Injection**: Service depends on DriverManager interface
6. **Single Responsibility**: Each class has one clear purpose

## References

- Original implementation: `app/Services/DataSourceService.php` (committed before refactoring)
- Driver contracts: `app/Services/DataSource/Contracts/`
- Driver implementations: `app/Services/DataSource/Drivers/`
- Parser implementations: `app/Services/DataSource/Parsers/`
- Factory classes: `app/Services/DataSource/DriverManager.php`, `DataParserFactory.php`

## Summary

The refactoring successfully transformed a 400+ line monolithic service into a clean, extensible architecture using proven design patterns. The new structure:

- ✅ Reduces complexity
- ✅ Improves testability
- ✅ Enables easy extension
- ✅ Maintains backward compatibility
- ✅ Follows SOLID principles
- ✅ Preserves all functionality

**Code Reduction**: 400+ lines → 116 lines (71% reduction)
**New Classes**: 13 focused classes replacing 1 monolithic class
**Test Coverage**: Easier to achieve with isolated components
