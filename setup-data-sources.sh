#!/bin/bash

echo "🚀 Data Sources Quick Start"
echo "============================"
echo ""

# Check if we're in the www directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the www directory"
    exit 1
fi

echo "📦 Step 1: Running migrations..."
php artisan migrate --force

echo ""
echo "🌱 Step 2: Seeding sample data sources..."
php artisan db:seed --class=DataSourceSeeder

echo ""
echo "🔄 Step 3: Fetching data from sources..."
php artisan data-sources:refresh --all

echo ""
echo "✅ Setup complete!"
echo ""
echo "📊 Data Sources Summary:"
echo "----------------------"
php artisan tinker --execute="
\$enabled = App\Models\DataSource::enabled()->count();
\$total = App\Models\DataSource::count();
echo \"✓ Total sources: \$total\n\";
echo \"✓ Enabled sources: \$enabled\n\";
echo \"✓ Disabled sources: \" . (\$total - \$enabled) . \"\n\";
"

echo ""
echo "🎯 Next Steps:"
echo "-------------"
echo "1. Start the Laravel server: php artisan serve"
echo "2. Start Vite dev server: npm run dev"
echo "3. Visit: http://localhost:8000/data-sources"
echo ""
echo "📚 Documentation:"
echo "----------------"
echo "- User Guide: docs/DATA_SOURCES_GUIDE.md"
echo "- Implementation: docs/DATA_SOURCES_IMPLEMENTATION.md"
echo ""
echo "🔧 Useful Commands:"
echo "------------------"
echo "  php artisan data-sources:refresh       # Refresh expired caches"
echo "  php artisan data-sources:refresh --all # Refresh all sources"
echo "  php artisan schedule:work              # Run background jobs"
echo ""
