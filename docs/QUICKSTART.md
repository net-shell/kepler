# Quick Start Guide

## Automated Setup (Recommended)

If you're starting from scratch, run the automated setup script:

```bash
cd /Users/boyan/Documents/_DEV/kepler/laravel-ai-search
chmod +x setup.sh
./setup.sh
```

This will:
- Install all Composer dependencies
- Install all Node dependencies
- Install Python dependencies
- Set up the `.env` file
- Create the SQLite database
- Run migrations
- Make scripts executable

## Manual Setup

If the automated setup doesn't work or you prefer manual setup:

### 1. Install Laravel (if not already installed)

```bash
# If starting from scratch, you can install Laravel first:
composer create-project laravel/laravel .

# Then copy over the custom files from this project
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install

# Python dependencies
pip3 install -r requirements.txt
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Update .env file to set:
# DB_CONNECTION=sqlite
# DB_DATABASE=/full/path/to/database/database.sqlite
```

### 4. Database Setup

```bash
php artisan migrate
```

### 5. Build Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### 6. Start the Application

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server (if using npm run dev)
npm run dev
```

### 7. Access the Application

Open your browser to: **http://localhost:8000**

## Testing the Components

### Test the Python Script

```bash
# Interactive mode
python3 scripts/ai_search_api.py

# Then type queries like:
> shipping information
> privacy policy
> exit
```

### Test the API

```bash
# Add a document
curl -X POST http://localhost:8000/api/data \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Document",
    "body": "This is a test document for the AI search system",
    "tags": ["test", "demo"]
  }'

# Search for documents
curl -X POST http://localhost:8000/api/search \
  -H "Content-Type: application/json" \
  -d '{
    "query": "test document",
    "limit": 5
  }'

# Get all documents
curl http://localhost:8000/api/data

# Get statistics
curl http://localhost:8000/api/search/stats
```

## Common Issues

### Issue: "Module not found" errors

**Solution**: Make sure you've run `npm install`

### Issue: Python script fails

**Solution**: 
```bash
pip3 install scikit-learn numpy
# or
pip install scikit-learn numpy
```

### Issue: Database errors

**Solution**:
```bash
# Make sure database file exists
touch database/database.sqlite

# Run migrations again
php artisan migrate:fresh
```

### Issue: Vite not connecting

**Solution**:
```bash
# Make sure Vite dev server is running
npm run dev

# Or build assets
npm run build
```

### Issue: CORS errors

**Solution**: The API routes are under `/api/` and should work with the same domain. If you're running frontend separately, you may need to configure CORS in Laravel.

## Project Overview

### Backend (Laravel)
- **Controllers**: `app/Http/Controllers/`
  - `SearchController.php` - Handles search requests
  - `DataController.php` - Manages document CRUD operations

- **Models**: `app/Models/Document.php`

- **Routes**: 
  - `routes/api.php` - API endpoints
  - `routes/web.php` - Web routes (serves Vue app)

- **Database**: SQLite at `database/database.sqlite`

### Frontend (Vue 3 + TypeScript)
- **Components**: `resources/js/components/`
  - `Dashboard.vue` - Main dashboard container
  - `SearchComponent.vue` - Search interface
  - `DataFeedComponent.vue` - Add new documents
  - `DocumentList.vue` - List and manage documents

- **Types**: `resources/js/types/index.ts`

- **Entry**: `resources/js/app.ts`

### Python Script
- `scripts/ai_search_api.py` - Enhanced AI search engine

## Development Workflow

1. Make changes to Vue components in `resources/js/components/`
2. Vite will auto-reload (if using `npm run dev`)
3. Make changes to Laravel controllers in `app/Http/Controllers/`
4. Test API endpoints with curl or the Vue frontend
5. Update Python script in `scripts/ai_search_api.py` if needed

## Production Deployment

1. Build assets:
   ```bash
   npm run build
   ```

2. Set environment:
   ```bash
   # Update .env
   APP_ENV=production
   APP_DEBUG=false
   ```

3. Optimize Laravel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. Set up a proper web server (nginx/Apache) instead of `php artisan serve`

## Support

For issues or questions, refer to:
- Laravel docs: https://laravel.com/docs
- Vue 3 docs: https://vuejs.org/
- scikit-learn docs: https://scikit-learn.org/

Enjoy building with AI Search! 🚀
