# Data Sources Feature Guide

## Overview

The Data Sources feature allows you to integrate external data into your AI search system. You can pull data from various sources including databases, URLs, and APIs with support for multiple authentication methods and data formats.

## Features

### Supported Source Types

1. **Database Sources** 🗄️
   - Execute SQL queries against any configured database connection
   - Supports MySQL, PostgreSQL, SQLite, and more
   - Results are automatically converted to searchable format

2. **URL Sources** 🌐
   - Fetch data from any publicly accessible URL
   - Auto-detect or manually specify data format
   - Supports: JSON, XML, CSV, RSS feeds, Plain Text
   - Perfect for static data files, RSS feeds, or webhooks

3. **API Sources** 🔌
   - Connect to REST APIs with full authentication support
   - Supported auth methods:
     - Bearer Token
     - API Key (custom header)
     - Basic Authentication
     - OAuth 2.0
   - Configure custom headers, request methods, and data extraction paths

### Key Features

- **Smart Caching**: Each source has configurable cache TTL (Time To Live)
- **Auto-Refresh**: Scheduled job automatically refreshes expired caches
- **Test Connections**: Test your configuration before saving
- **Preview Data**: View sample data from each source
- **Enable/Disable**: Toggle sources on/off without deletion
- **Combined Search**: Search across all enabled sources simultaneously

## Getting Started

### 1. Access Data Sources

Navigate to the Data Sources page:
- From Dashboard: Click "🔌 Data Sources" in the header
- Direct URL: `/data-sources`

### 2. Create a Data Source

Click "➕ Add Data Source" and fill in:

- **Name**: Descriptive name for your source
- **Type**: Choose database, URL, or API
- **Description**: Optional notes about the source
- **Cache TTL**: How long to cache data (in seconds)

### 3. Configure Source-Specific Settings

#### For URL Sources:
```
URL: https://example.com/data.json
Format: JSON (or XML, CSV, RSS, Text)
```

#### For API Sources:
```
URL: https://api.example.com/v1/items
Method: GET or POST
Authentication: Choose from None, Bearer, API Key, Basic, OAuth2
Format: JSON or XML
Data Path: (optional) Path to items array, e.g., "data.items"
```

#### For Database Sources:
```
SQL Query: SELECT id, title, content, tags FROM articles
Connection: (optional) mysql, pgsql, sqlite
```

### 4. Test Connection

Before saving, click "🧪 Test Connection" to verify:
- URL is accessible
- Authentication works
- Data format is correct
- Sample data is returned

### 5. Save and Enable

Once tested successfully, click "Create" to save the data source. It will be enabled by default.

## Managing Data Sources

### View Sources

The main Data Sources page shows all configured sources with:
- Source icon and type
- Enabled/disabled status
- Cache TTL and last cached time
- Cache validity status

### Actions Available

- **👁️ Preview**: View sample data from the source
- **🔄 Refresh**: Manually refresh the cache
- **⏸️ Disable / ▶️ Enable**: Toggle the source on/off
- **✏️ Edit**: Modify source configuration
- **🗑️ Delete**: Permanently remove the source

## API Endpoints

### Data Sources Management

```
GET    /api/data-sources          - List all sources
POST   /api/data-sources          - Create new source
GET    /api/data-sources/{id}     - Get source details
PUT    /api/data-sources/{id}     - Update source
DELETE /api/data-sources/{id}     - Delete source
POST   /api/data-sources/test     - Test connection
POST   /api/data-sources/{id}/refresh    - Refresh cache
POST   /api/data-sources/{id}/toggle     - Enable/disable
GET    /api/data-sources/{id}/data       - Get cached data
GET    /api/data-sources/{id}/preview    - Get preview
```

### Data Feed

```
GET /api/feed          - Get all data from all sources
GET /api/feed/stats    - Get feed statistics
```

Query parameters for `/api/feed`:
- `include_documents` (boolean, default: true) - Include database documents
- `include_data_sources` (boolean, default: true) - Include external sources
- `use_cache` (boolean, default: true) - Use cached data

### Search with Data Sources

The search endpoint automatically includes data from all enabled sources:

```
POST /api/search
{
  "query": "search term",
  "limit": 10,
  "include_documents": true,
  "include_data_sources": true
}
```

## Automatic Cache Management

### Scheduled Job

The system automatically runs a job every 5 minutes to:
- Check all enabled data sources
- Identify sources with expired caches
- Refresh data from those sources
- Log success/failure for each source

### Manual Refresh

You can also manually refresh any source:
1. Go to Data Sources page
2. Find the source you want to refresh
3. Click "🔄 Refresh"
4. Cache will be updated immediately

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# Default cache TTL for new data sources (seconds)
DATA_SOURCE_CACHE_TTL=3600

# HTTP timeout for URL/API requests (seconds)
DATA_SOURCE_HTTP_TIMEOUT=30

# Maximum items per source (null for unlimited)
DATA_SOURCE_MAX_ITEMS=null
```

### Config File

Edit `config/datasources.php` to customize:
- Default cache TTL
- Supported source types
- Authentication methods
- Data formats
- HTTP timeout
- Max items per source

## Examples

### Example 1: RSS Feed

```
Name: Tech News Feed
Type: URL
URL: https://news.ycombinator.com/rss
Format: RSS
Cache TTL: 1800 (30 minutes)
```

### Example 2: API with Bearer Token

```
Name: GitHub Repositories
Type: API
URL: https://api.github.com/user/repos
Method: GET
Auth Type: Bearer Token
Token: ghp_your_token_here
Format: JSON
Cache TTL: 3600 (1 hour)
```

### Example 3: Database Query

```
Name: Product Catalog
Type: Database
Query: SELECT id, name, description, category FROM products WHERE active = 1
Connection: mysql
Cache TTL: 7200 (2 hours)
```

### Example 4: Public JSON API

```
Name: JSONPlaceholder Posts
Type: API
URL: https://jsonplaceholder.typicode.com/posts
Method: GET
Auth Type: None
Format: JSON
Cache TTL: 3600 (1 hour)
```

### Example 5: API with Nested Data

```
Name: API with Nested Response
Type: API
URL: https://api.example.com/v1/items
Method: GET
Auth Type: API Key
API Key Header: X-API-Key
API Key: your-api-key
Format: JSON
Data Path: data.results
Cache TTL: 1800 (30 minutes)
```

## Troubleshooting

### Cache Not Refreshing

1. Check if the source is enabled
2. Verify the cache TTL hasn't been set too high
3. Check Laravel logs for job errors: `storage/logs/laravel.log`
4. Manually trigger refresh from the UI

### Connection Test Fails

1. Verify the URL is accessible
2. Check authentication credentials
3. Ensure the data format matches the actual response
4. Check for firewall or network restrictions

### No Data in Search Results

1. Confirm the source is enabled
2. Check if cache has been populated (view preview)
3. Verify data format is correct
4. Check if source has any data

### Performance Issues

1. Reduce cache TTL for less critical sources
2. Limit the number of items returned from sources
3. Disable sources that aren't needed
4. Use more specific database queries

## Best Practices

1. **Set Appropriate Cache TTLs**
   - Frequently changing data: 5-30 minutes
   - Hourly updates: 1 hour
   - Daily updates: 6-24 hours
   - Static data: Several days

2. **Test Before Saving**
   - Always test connections before creating sources
   - Verify sample data looks correct
   - Check for authentication errors

3. **Monitor Cache Status**
   - Regularly check the Data Sources page
   - Look for sources with expired caches
   - Review cache refresh logs

4. **Optimize Queries**
   - For database sources, use indexed columns
   - Limit result sets to necessary data
   - Use WHERE clauses to filter data

5. **Security**
   - Store API keys securely
   - Use environment variables for sensitive data
   - Regularly rotate authentication tokens
   - Review access logs periodically

## Advanced Features

### Data Path Extraction

For APIs that return nested data, use the Data Path field to extract the items array:

Response:
```json
{
  "status": "success",
  "data": {
    "items": [
      {"id": 1, "title": "Item 1"},
      {"id": 2, "title": "Item 2"}
    ]
  }
}
```

Data Path: `data.items`

### Custom Headers for APIs

Add custom headers in the config JSON for advanced API requirements.

### Multiple Database Connections

Configure different database connections in `config/database.php` and specify them in your database sources.

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Review the browser console for frontend errors
3. Test connections individually
4. Verify API credentials and permissions
