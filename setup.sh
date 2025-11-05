#!/bin/bash

# AI Search Laravel + Vue Setup Script
# This script sets up the complete Laravel + Vue 3 + TypeScript application

echo "🚀 AI Search Setup Script"
echo "=========================="
echo ""

# Check if we're in the right directory
if [ ! -f "package.json" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

# Check for required commands
command -v php >/dev/null 2>&1 || { echo "❌ PHP is required but not installed. Aborting." >&2; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "❌ Composer is required but not installed. Aborting." >&2; exit 1; }
command -v node >/dev/null 2>&1 || { echo "❌ Node.js is required but not installed. Aborting." >&2; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "❌ npm is required but not installed. Aborting." >&2; exit 1; }
command -v python3 >/dev/null 2>&1 || { echo "❌ Python 3 is required but not installed. Aborting." >&2; exit 1; }

echo "✅ All required commands found"
echo ""

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Composer install failed. You may need to install Laravel manually:"
    echo "   composer create-project laravel/laravel ."
    exit 1
fi

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ npm install failed"
    exit 1
fi

# Install Python dependencies
echo "🐍 Installing Python dependencies..."
pip3 install -r requirements.txt

if [ $? -ne 0 ]; then
    echo "⚠️  Warning: Python dependencies installation failed"
    echo "   You may need to install manually: pip3 install scikit-learn numpy"
fi

# Set up environment
if [ ! -f ".env" ]; then
    echo "⚙️  Setting up environment..."
    cp .env.example .env
    php artisan key:generate
else
    echo "ℹ️  .env file already exists, skipping"
fi

# Create SQLite database
echo "🗄️  Creating SQLite database..."
touch database/database.sqlite

# Update .env for SQLite
if grep -q "DB_DATABASE=" .env; then
    # Update existing line
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' "s|DB_DATABASE=.*|DB_DATABASE=$(pwd)/database/database.sqlite|" .env
    else
        # Linux
        sed -i "s|DB_DATABASE=.*|DB_DATABASE=$(pwd)/database/database.sqlite|" .env
    fi
else
    # Add new line
    echo "DB_DATABASE=$(pwd)/database/database.sqlite" >> .env
fi

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo "❌ Migration failed"
    exit 1
fi

# Make Python script executable
chmod +x scripts/ai_search_api.py

echo ""
echo "✅ Setup complete!"
echo ""
echo "📝 Next steps:"
echo "   1. Start the Laravel server:"
echo "      php artisan serve"
echo ""
echo "   2. In another terminal, start the Vite dev server:"
echo "      npm run dev"
echo ""
echo "   3. Open your browser to:"
echo "      http://localhost:8000"
echo ""
echo "   4. Test the Python script:"
echo "      python3 scripts/ai_search_api.py"
echo ""
echo "🎉 Happy coding!"
