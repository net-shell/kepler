# AI Search - Laravel + Vue 3 + TypeScript

A modern full-stack application combining Laravel backend with Vue 3 (TypeScript) frontend for semantic document search using TF-IDF and cosine similarity.

## Features

- 🔍 **Semantic Search**: TF-IDF-based search with cosine similarity scoring
- 📊 **User Dashboard**: Interactive Vue 3 dashboard with TypeScript
- 🗄️ **SQLite Database**: Lightweight database for document storage
- 🔌 **REST API**: Complete API for searching and managing documents
- 🐍 **Python Integration**: Enhanced AI search script with API support
- 🎨 **Modern UI**: Beautiful, responsive interface with gradient designs

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
laravel-ai-search/
├── app/
│   ├── Http/Controllers/
│   │   ├── SearchController.php    # Search API controller
│   │   └── DataController.php      # Data management controller
│   └── Models/
│       └── Document.php             # Document model
├── database/
│   └── migrations/
│       └── create_documents_table.php
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── Dashboard.vue        # Main dashboard
│   │   │   ├── SearchComponent.vue  # Search interface
│   │   │   ├── DataFeedComponent.vue # Data input form
│   │   │   └── DocumentList.vue     # Document list view
│   │   ├── types/
│   │   │   └── index.ts            # TypeScript definitions
│   │   ├── app.ts                  # Vue app entry point
│   │   └── style.css               # Global styles
│   └── views/
│       └── app.blade.php           # Main HTML template
├── routes/
│   ├── api.php                     # API routes
│   └── web.php                     # Web routes
├── scripts/
│   └── ai_search_api.py           # Enhanced Python search script
├── vite.config.ts                 # Vite configuration
├── tsconfig.json                  # TypeScript configuration
└── package.json                   # Node dependencies
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
