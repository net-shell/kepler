# API Feed Integration

## Summary

Both the frontend (`DocumentList.vue`) and the Python AI search script (`ai_search_api.py`) have been updated to use the `/api/feed` endpoint to retrieve data.

## Changes Made

### 1. Frontend (DocumentList.vue)

**Changed:** `loadAllDocuments()` function now fetches from `/api/feed` instead of `/api/data`

```typescript
const loadAllDocuments = async () => {
    try {
        loading.value = true;
        const response = await fetch('/api/feed');  // Changed from '/api/data'
        const data = await response.json();
        documents.value = data.data || [];
    } catch (error) {
        console.error('Failed to load documents:', error);
    } finally {
        loading.value = false;
    }
};
```

**Benefits:**
- Now fetches combined data from both database documents and external data sources
- Provides a unified view of all available data
- Automatically includes cached data from enabled data sources

### 2. Backend Script (ai_search_api.py)

**Added:** `load_documents_from_feed()` function to fetch from the API

```python
def load_documents_from_feed() -> List[Dict]:
    """Load documents from API feed endpoint."""
    api_url = get_api_url()
    
    try:
        response = requests.get(f"{api_url}/api/feed")
        response.raise_for_status()
        
        data = response.json()
        
        if not data.get('success'):
            raise RuntimeError(f"API returned error: {data.get('message', 'Unknown error')}")
        
        return data.get('data', [])
    
    except requests.RequestException as e:
        raise RuntimeError(f"Failed to fetch from API feed: {str(e)}")
```

**Added:** `get_api_url()` function to get the API URL from environment

```python
def get_api_url() -> str:
    """Get API URL from environment or use default."""
    # Loads from APP_URL in .env or defaults to http://localhost:8000
    api_url = os.getenv('APP_URL', 'http://localhost:8000')
    return api_url.rstrip('/')
```

**Updated:** `main()` function to use API feed as fallback

```python
# Check if data is provided directly in input
if 'data' in input_data and input_data['data']:
    # Use provided data
    data = input_data['data']
else:
    # Fall back to loading from API feed
    data = load_documents_from_feed()
```

**Updated:** `interactive()` mode with better error handling

```python
def interactive():
    """Interactive mode for testing with live API feed."""
    try:
        documents = load_documents_from_feed()
    except Exception as api_error:
        print(f"Failed to load from API: {api_error}")
        print("\nFalling back to database...")
        documents = load_documents_from_db(db_path)
```

## Dependencies

The `requests` library is required and is already listed in `requirements.txt`:

```
scikit-learn>=1.3.0
numpy>=1.24.0
requests>=2.31.0
python-dotenv>=1.0.0
```

## Configuration

### Environment Variables

Add to your `.env` file:

```env
APP_URL=http://localhost:8000
```

If not set, it defaults to `http://localhost:8000`.

## Usage

### Frontend

The DocumentList component will automatically load data from `/api/feed` when mounted. No changes needed in usage.

### Python Script (API Mode)

```bash
# With data provided in input
echo '{"query": "search term", "limit": 5, "data": [...]}' | python3 scripts/ai_search_api.py

# Without data (will fetch from /api/feed)
echo '{"query": "search term", "limit": 5}' | python3 scripts/ai_search_api.py
```

### Python Script (Interactive Mode)

```bash
# Will attempt to load from API first, then fallback to database
python3 scripts/ai_search_api.py
```

## API Feed Endpoint

The `/api/feed` endpoint returns data in this format:

```json
{
  "success": true,
  "count": 150,
  "data": [
    {
      "id": 1,
      "title": "Document Title",
      "body": "Document content...",
      "tags": ["tag1", "tag2"],
      "metadata": {},
      "_source_type": "document",
      "_source_name": "Database Documents"
    },
    {
      "title": "External Data Item",
      "body": "Content from external source...",
      "tags": [],
      "metadata": {},
      "_source_type": "api",
      "_source_name": "External API"
    }
  ]
}
```

## Benefits

1. **Unified Data Access**: Both frontend and backend use the same data source
2. **Consistency**: Search results match what users see in the UI
3. **Flexibility**: Supports both database documents and external data sources
4. **Fallback Support**: Python script can fallback to direct database access if API is unavailable
5. **Caching**: Leverages server-side caching for external data sources

## Testing

1. Ensure Laravel server is running: `php artisan serve`
2. Test API endpoint: `curl http://localhost:8000/api/feed`
3. Test Python script: `python3 scripts/ai_search_api.py`
4. Test frontend: Visit the documents page in your browser

## Troubleshooting

### Python Script Errors

If you see API connection errors:
- Ensure the Laravel server is running
- Check that `APP_URL` in `.env` matches your server URL
- Verify the `/api/feed` endpoint is accessible

### Frontend Errors

If documents don't load:
- Check browser console for errors
- Verify the `/api/feed` endpoint returns data
- Check that data sources are enabled if you expect external data
