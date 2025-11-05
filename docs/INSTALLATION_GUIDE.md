# 🎯 Complete Installation & Usage Guide

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation Methods](#installation-methods)
3. [Running the Application](#running-the-application)
4. [Using the Application](#using-the-application)
5. [API Usage](#api-usage)
6. [Advanced Features](#advanced-features)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before you begin, ensure you have the following installed:

### Required Software
- **PHP** 8.1 or higher
  ```bash
  php --version
  ```
- **Composer** (PHP dependency manager)
  ```bash
  composer --version
  ```
- **Node.js** 18 or higher
  ```bash
  node --version
  ```
- **npm** (comes with Node.js)
  ```bash
  npm --version
  ```
- **Python** 3.8 or higher
  ```bash
  python3 --version
  ```
- **pip3** (Python package manager)
  ```bash
  pip3 --version
  ```

### Installing Prerequisites (macOS)
```bash
# Install Homebrew (if not installed)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP
brew install php

# Install Composer
brew install composer

# Install Node.js
brew install node

# Python 3 comes with macOS, but you can update it
brew install python3
```

---

## Installation Methods

### Method 1: Automated Setup (Recommended)

```bash
# Navigate to project directory
cd /Users/boyan/Documents/_DEV/kepler/laravel-ai-search

# Make setup script executable
chmod +x setup.sh

# Run setup script
./setup.sh
```

This will:
- ✅ Install PHP dependencies (Composer)
- ✅ Install JavaScript dependencies (npm)
- ✅ Install Python dependencies (pip)
- ✅ Create .env file
- ✅ Generate application key
- ✅ Create SQLite database
- ✅ Run migrations
- ✅ Make scripts executable

### Method 2: Manual Setup

#### Step 1: Install Laravel First (if needed)
```bash
# Create fresh Laravel project
composer create-project laravel/laravel temp-laravel

# Move Laravel core files to our project
cp -r temp-laravel/bootstrap .
cp -r temp-laravel/config .
cp -r temp-laravel/storage .
cp temp-laravel/artisan .
cp temp-laravel/server.php .
cp temp-laravel/.env.example .

# Clean up
rm -rf temp-laravel
```

#### Step 2: Install Dependencies
```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install

# Python dependencies
pip3 install -r requirements.txt
```

#### Step 3: Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
touch database/database.sqlite

# Edit .env and set:
# DB_CONNECTION=sqlite
# DB_DATABASE=/full/path/to/laravel-ai-search/database/database.sqlite
```

#### Step 4: Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

### Method 3: Using Makefile

```bash
# Install everything
make install

# Setup database
make migrate

# Seed data
make seed
```

---

## Running the Application

### Option 1: Development Mode (Recommended for Development)

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
# Starts at http://localhost:8000
```

**Terminal 2 - Vite Dev Server (Hot Reload):**
```bash
npm run dev
# Enables hot module replacement
```

### Option 2: Production Build

```bash
# Build assets once
npm run build

# Start Laravel
php artisan serve
```

### Option 3: Using Makefile

```bash
# Start both servers
make dev
```

---

## Using the Application

### 1. Access the Dashboard

Open your browser to: **http://localhost:8000**

### 2. Dashboard Tabs

#### 🔍 Search Tab
- **Purpose**: Search through your documents
- **Features**:
  - Enter any query
  - Adjust result limit (5, 10, 20, or 50)
  - View results with similarity scores
  - See document tags and metadata
  - Results ranked by relevance

**Example Queries:**
- "privacy policy"
- "how to get a refund"
- "shipping information"
- "customer support"

#### ➕ Add Data Tab
- **Purpose**: Create new documents
- **Required Fields**:
  - Title (document title)
  - Body (document content)
- **Optional Fields**:
  - Tags (add relevant keywords)
  - Metadata (custom key-value pairs)

**Example Document:**
```
Title: Customer Service Hours
Body: Our support team is available Monday-Friday, 9am-5pm EST. 
      Weekend support available for premium customers.
Tags: support, hours, customer-service
Metadata: 
  category: support
  priority: high
```

#### 📋 Documents Tab
- **Purpose**: View and manage all documents
- **Features**:
  - Paginated list (10 per page)
  - View creation dates
  - See tags
  - Delete documents
  - Navigate between pages

### 3. Loading Demo Data

**Method 1: Using Seeder (Laravel)**
```bash
php artisan db:seed --class=DocumentSeeder
# Loads 10 sample documents
```

**Method 2: Using Python Script (via API)**
```bash
# Make sure Laravel server is running first!
python3 scripts/demo_data_loader.py
# Loads 10 demo documents via API
```

**Method 3: Using Makefile**
```bash
make seed    # Uses Laravel seeder
# OR
make demo    # Uses Python script
```

---

## API Usage

### Base URL
```
http://localhost:8000/api
```

### 1. Search Documents

**Endpoint:** `POST /api/search`

**Request:**
```json
{
  "query": "customer support",
  "limit": 5
}
```

**Response:**
```json
{
  "success": true,
  "results": [
    {
      "score": 0.856,
      "record": {
        "id": 1,
        "title": "Customer Support",
        "body": "24/7 customer support available...",
        "tags": ["support", "help"],
        "metadata": {"category": "support"}
      },
      "rank": 1
    }
  ],
  "query": "customer support"
}
```

### 2. Get Statistics

**Endpoint:** `GET /api/search/stats`

**Response:**
```json
{
  "total_documents": 10,
  "latest_document": {...},
  "oldest_document": {...}
}
```

### 3. Create Document

**Endpoint:** `POST /api/data`

**Request:**
```json
{
  "title": "New Document",
  "body": "Document content here",
  "tags": ["tag1", "tag2"],
  "metadata": {"key": "value"}
}
```

### 4. List Documents

**Endpoint:** `GET /api/data?page=1&per_page=10`

### 5. Get Single Document

**Endpoint:** `GET /api/data/{id}`

### 6. Update Document

**Endpoint:** `PUT /api/data/{id}`

### 7. Delete Document

**Endpoint:** `DELETE /api/data/{id}`

### cURL Examples

```bash
# Search
curl -X POST http://localhost:8000/api/search \
  -H "Content-Type: application/json" \
  -d '{"query": "support", "limit": 3}'

# Create document
curl -X POST http://localhost:8000/api/data \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Doc",
    "body": "Content",
    "tags": ["test"]
  }'

# Get all documents
curl http://localhost:8000/api/data

# Delete document
curl -X DELETE http://localhost:8000/api/data/1
```

---

## Advanced Features

### 1. Testing Python Script Standalone

**Interactive Mode:**
```bash
python3 scripts/ai_search_api.py
```

Then type queries:
```
> customer support
> privacy policy
> exit
```

**API Mode (stdin/stdout):**
```bash
echo '{
  "data": [
    {"id": 1, "title": "Test", "body": "Content", "tags": ["test"]}
  ],
  "query": "test",
  "limit": 5
}' | python3 scripts/ai_search_api.py
```

### 2. View Search Statistics

```bash
php artisan search:stats
```

Shows:
- Total documents
- Documents with tags
- Recent additions
- Latest document
- Top tags

### 3. Database Management

```bash
# Fresh migration (destroys all data!)
php artisan migrate:fresh

# Fresh migration + seed
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

### 4. Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Customizing the Search Algorithm

Edit `scripts/ai_search_api.py`:

```python
# Change n-gram range (line ~25)
ngram_range=(1, 2)  # unigrams and bigrams
# Try: (1, 3) for trigrams

# Adjust field weights (in _flatten method)
# Title gets 3x weight by default
parts.extend([title, title, title])
# Try: [title, title] for 2x weight

# Modify minimum score threshold
results = engine.search(query, k=limit, min_score=0.1)
# Try: min_score=0.2 for stricter results
```

---

## Troubleshooting

### Problem: "Laravel not found" or composer errors

**Solution:**
```bash
# Install Laravel first
composer create-project laravel/laravel .
# Then copy over our custom files
```

### Problem: "Cannot connect to database"

**Solution:**
```bash
# Recreate database
rm database/database.sqlite
touch database/database.sqlite

# Update .env
DB_CONNECTION=sqlite
DB_DATABASE=/full/path/to/database/database.sqlite

# Migrate again
php artisan migrate
```

### Problem: "Module 'vue' not found"

**Solution:**
```bash
# Reinstall node modules
rm -rf node_modules package-lock.json
npm install
```

### Problem: Python script fails

**Solution:**
```bash
# Reinstall Python packages
pip3 install --upgrade scikit-learn numpy requests

# Check Python version
python3 --version  # Should be 3.8+
```

### Problem: "Address already in use"

**Solution:**
```bash
# Laravel using port 8000
php artisan serve --port=8001

# Or kill existing process
lsof -ti:8000 | xargs kill -9
```

### Problem: Vite not connecting

**Solution:**
```bash
# Build assets instead of using dev server
npm run build

# Clear vite cache
rm -rf node_modules/.vite
npm run dev
```

### Problem: Search returns no results

**Solution:**
1. Check if documents exist: Visit Documents tab
2. Try broader queries: "customer" instead of "customer support help"
3. Check Python is working:
   ```bash
   python3 scripts/ai_search_api.py
   ```
4. Check server logs for errors

### Problem: Assets not loading

**Solution:**
```bash
# Clear Laravel cache
php artisan cache:clear

# Rebuild assets
npm run build

# Check public/build directory exists
ls -la public/build
```

---

## Quick Reference

### Essential Commands

```bash
# Setup
./setup.sh                      # Automated setup
make install                    # Install dependencies

# Development
php artisan serve              # Start Laravel
npm run dev                    # Start Vite
make dev                       # Start both

# Database
php artisan migrate            # Run migrations
php artisan db:seed           # Seed data
make seed                     # Seed via Makefile

# Data
python3 scripts/demo_data_loader.py   # Load demo data
php artisan search:stats              # View stats

# Build
npm run build                  # Production build
make build                     # Build via Makefile

# Cleanup
make clean                     # Clean build files
```

### Important URLs

- **Application**: http://localhost:8000
- **API Base**: http://localhost:8000/api
- **Vite Dev Server**: http://localhost:5173 (when running npm run dev)

---

## Next Steps

1. ✅ Complete installation
2. ✅ Load demo data
3. ✅ Try searching
4. ✅ Add your own documents
5. ✅ Explore the API
6. ✅ Customize to your needs

**Happy searching! 🚀**
