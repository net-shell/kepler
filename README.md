# AI Search - Laravel + Vue 3 + TypeScript

A modern full-stack application combining Laravel backend with Vue 3 (TypeScript) frontend for semantic document search using TF-IDF and cosine similarity.

## Features

- 🔍 **Semantic Search**: TF-IDF-based search with cosine similarity scoring
- � **Data Sources**: Connect to external APIs, URLs, RSS feeds, and databases
- �📊 **User Dashboard**: Interactive Vue 3 dashboard with TypeScript
- 🗄️ **SQLite Database**: Lightweight database for document storage
- 🌐 **REST API**: Complete API for searching and managing documents
- 🐍 **Python Integration**: Enhanced AI search script with API support
- 🎨 **Modern UI**: Beautiful, responsive interface with gradient designs
- 🔄 **Smart Caching**: Configurable cache TTL with automatic background refresh
- 🔐 **API Authentication**: Support for Bearer, API Key, Basic Auth, and OAuth2

## Tech Stack

### Backend
- Laravel 10+
- SQLite Database
- PHP 8.1+

### Frontend
- Vue 3 with Composition API
- TypeScript
- Vite
- Modern CSS with gradients and animations

### AI Search
- Python 3.8+
- scikit-learn (TF-IDF vectorization)
- NumPy (numerical operations)

## New: Data Sources Feature 🔌

**Search across multiple data sources simultaneously!**

The Data Sources feature allows you to integrate external data into your AI search:

### Supported Source Types

1. **Database** 🗄️ - Execute SQL queries against any database
2. **URL/File** 🌐 - Fetch JSON, XML, CSV, RSS feeds, or plain text
3. **API** 🔌 - Connect to REST APIs with authentication

### Quick Start

```bash
# Seed sample data sources
php artisan db:seed --class=DataSourceSeeder

# Refresh all data sources
php artisan data-sources:refresh --all

# View in browser
# Navigate to: /data-sources
```

### Example Uses

- Fetch product catalogs from external APIs
- Import RSS news feeds
- Query remote databases
- Aggregate data from multiple sources
- Cache frequently accessed external data

**See [docs/DATA_SOURCES_GUIDE.md](docs/DATA_SOURCES_GUIDE.md) for detailed documentation.**


## Installation

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js >= 18
- npm or yarn
- Python 3.8+
- pip

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/net-shell/kepler.git
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Install Python dependencies**
   ```bash
   pip3 install scikit-learn numpy dotenv
   ```

5. **Set up environment**
   ```bash
   cp .env.example .env
   python3 -m venv scripts/venv
   php artisan key:generate
   ```

6. **Create SQLite database**
   ```bash
   touch database/database.sqlite
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Build frontend assets**
   ```bash
   npm run build
   # Or for development with hot reload:
   npm run dev
   ```

9. **Start the Laravel server**
   ```bash
   php artisan serve
   ```

10. **Access the application**
    Open your browser to: http://localhost:8000

## API Endpoints

### Search Endpoints

- `POST /api/search` - Search documents
  ```json
  {
    "query": "your search query",
    "limit": 5
  }
  ```

- `GET /api/search/stats` - Get search statistics

### Data Management Endpoints

- `GET /api/data` - List all documents (paginated)
- `POST /api/data` - Create a new document
  ```json
  {
    "title": "Document Title",
    "body": "Document content",
    "tags": ["tag1", "tag2"],
    "metadata": {"key": "value"}
  }
  ```

- `POST /api/data/batch` - Batch create documents
- `GET /api/data/{id}` - Get a specific document
- `PUT /api/data/{id}` - Update a document
- `DELETE /api/data/{id}` - Delete a document

## Python Script Usage

### Interactive Demo Mode
```bash
python3 scripts/ai_search_api.py
```

### API Mode (via Laravel)
The script is automatically called by Laravel when performing searches.

### Standalone API Mode
```bash
echo '{"data": [...], "query": "search term", "limit": 5}' | python3 scripts/ai_search_api.py
```

## Project Structure

```
kepler/
├── ai/
│   └── ai_search.py                    # AI search implementation
│
├── www/                                # Main Laravel application
│   ├── app/
│   │   ├── Actions/
│   │   │   └── Fortify/               # Authentication actions
│   │   ├── Console/
│   │   │   ├── Commands/
│   │   │   │   └── SearchStats.php    # Search statistics command
│   │   │   └── Kernel.php
│   │   ├── Exceptions/
│   │   │   └── Handler.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Controller.php
│   │   │   │   ├── DataController.php         # Document CRUD & bulk upload
│   │   │   │   ├── SearchController.php       # AI search API
│   │   │   │   └── Settings/                  # User settings controllers
│   │   │   ├── Middleware/                    # HTTP middleware
│   │   │   ├── Requests/
│   │   │   │   └── Settings/                  # Form requests
│   │   │   └── Kernel.php
│   │   ├── Models/
│   │   │   ├── Document.php           # Document model with folder path
│   │   │   └── User.php               # User model with 2FA
│   │   ├── Providers/
│   │   │   ├── AppServiceProvider.php
│   │   │   ├── FortifyServiceProvider.php
│   │   │   └── RouteServiceProvider.php
│   │   └── Services/
│   │       └── FileProcessingService.php      # File parsing service
│   │
│   ├── config/
│   │   ├── aisearch.php               # AI search configuration
│   │   ├── app.php
│   │   ├── auth.php
│   │   ├── database.php
│   │   ├── fortify.php
│   │   └── inertia.php
│   │
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── create_users_table.php
│   │   │   ├── create_documents_table.php
│   │   │   ├── add_two_factor_columns_to_users_table.php
│   │   │   └── add_path_to_documents_table.php
│   │   ├── factories/
│   │   └── seeders/
│   │
│   ├── docs/
│   │   ├── ARCHITECTURE.md            # System architecture
│   │   ├── BULK_UPLOAD_GUIDE.md       # Bulk upload documentation
│   │   ├── BULK_UPLOAD_QUICKSTART.md  # Quick start guide
│   │   ├── DOCUMENTATION_INDEX.md     # Documentation index
│   │   ├── FOLDER_TREE_FEATURE.md     # Folder tree documentation
│   │   ├── INSTALLATION_GUIDE.md      # Installation guide
│   │   ├── PROJECT_SUMMARY.md         # Project summary
│   │   ├── QUICKSTART.md              # Quick start
│   │   ├── README_BULK_UPLOAD.md      # Bulk upload README
│   │   └── VISUAL_GUIDE.md            # Visual guide
│   │
│   ├── resources/
│   │   ├── css/
│   │   │   └── app.css
│   │   ├── js/
│   │   │   ├── actions/               # Auto-generated Wayfinder actions
│   │   │   ├── components/
│   │   │   │   ├── AlertError.vue
│   │   │   │   ├── AppContent.vue
│   │   │   │   ├── AppHeader.vue
│   │   │   │   ├── AppLogo.vue
│   │   │   │   ├── AppShell.vue
│   │   │   │   ├── AppSidebar.vue
│   │   │   │   ├── BulkUploadComponent.vue    # Bulk upload interface
│   │   │   │   ├── Dashboard.vue              # Main dashboard
│   │   │   │   ├── DataFeedComponent.vue      # Data input form
│   │   │   │   ├── DocumentCard.vue
│   │   │   │   ├── DocumentList.vue           # Document list
│   │   │   │   ├── FolderTree.vue             # Folder navigation
│   │   │   │   ├── SearchComponent.vue        # Search interface
│   │   │   │   ├── TwoFactorSetupModal.vue    # 2FA setup
│   │   │   │   └── ui/                        # shadcn/ui components
│   │   │   ├── composables/           # Vue composables
│   │   │   ├── layouts/               # Page layouts
│   │   │   │   ├── AppLayout.vue
│   │   │   │   ├── AuthLayout.vue
│   │   │   │   └── settings/
│   │   │   ├── lib/
│   │   │   │   └── utils.ts
│   │   │   ├── pages/
│   │   │   │   ├── auth/              # Authentication pages
│   │   │   │   ├── settings/          # User settings pages
│   │   │   │   ├── Dashboard.vue
│   │   │   │   ├── DocumentShow.vue
│   │   │   │   └── Welcome.vue
│   │   │   ├── routes/                # Wayfinder routes
│   │   │   ├── types/                 # TypeScript definitions
│   │   │   ├── wayfinder/             # Wayfinder config
│   │   │   ├── app.ts                 # Vue app entry
│   │   │   └── ssr.ts                 # SSR entry
│   │   └── views/
│   │       └── app.blade.php
│   │
│   ├── routes/
│   │   ├── api.php                    # API routes
│   │   ├── console.php                # Console routes
│   │   ├── settings.php               # Settings routes
│   │   └── web.php                    # Web routes
│   │
│   ├── scripts/
│   │   └── ai_search_api.py           # Python AI search script
│   │
│   ├── storage/
│   │   ├── app/
│   │   │   ├── public/
│   │   │   └── uploads/               # Uploaded files
│   │   ├── framework/
│   │   └── logs/
│   │
│   ├── tests/
│   │   ├── Feature/
│   │   ├── Unit/
│   │   └── Pest.php
│   │
│   ├── composer.json                  # PHP dependencies
│   ├── package.json                   # Node dependencies
│   ├── vite.config.ts                 # Vite configuration
│   ├── tsconfig.json                  # TypeScript configuration
│   ├── components.json                # shadcn/ui config
│   └── README.md                      # This file
```

## Development

### Run in development mode
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server (hot reload)
npm run dev
```

### Build for production
```bash
npm run build
```

### Test the Python script
```bash
python3 scripts/ai_search_api.py
```

## Features Explained

### 1. Dashboard Tab
- View total document count
- Quick statistics overview

### 2. Search Tab
- Enter search queries
- Adjust result limit
- View ranked results with scores
- See document tags and metadata

### 3. Add Data Tab
- Create new documents
- Add tags
- Add custom metadata
- Batch operations support

### 4. Documents Tab
- Browse all documents
- Pagination support
- Delete documents
- View creation dates

## Customization

### Modify Search Algorithm
Edit `scripts/ai_search_api.py` to adjust:
- TF-IDF parameters
- Scoring weights (title, body, tags)
- n-gram ranges
- Minimum score thresholds

### Update UI Theme
Edit `resources/js/style.css` and component styles to change:
- Color schemes
- Gradients
- Animations
- Layout

### Add New Features
1. Create new Vue components in `resources/js/components/`
2. Add new API routes in `routes/api.php`
3. Create corresponding controllers in `app/Http/Controllers/`

## Troubleshooting

### Python script not found
- Ensure Python 3 is installed and in PATH
- Verify script path in `SearchController.php`

### Database errors
- Check SQLite database exists: `database/database.sqlite`
- Run migrations: `php artisan migrate`

### Frontend not loading
- Build assets: `npm run build`
- Check Vite is running: `npm run dev`
- Clear cache: `php artisan cache:clear`

## License

This project is open-source and available for educational purposes.

## Contributing

Feel free to submit issues and enhancement requests!
