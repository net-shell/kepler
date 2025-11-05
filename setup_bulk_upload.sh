#!/bin/bash

# Bulk Upload Feature - Post-Installation Setup Script
# Run this script after adding the bulk upload feature

echo "🚀 Bulk Upload Feature - Setup Script"
echo "======================================"
echo ""

# Check if we're in the www directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the www directory"
    exit 1
fi

echo "📦 Checking PHP dependencies..."
composer show phpoffice/phpspreadsheet > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ phpoffice/phpspreadsheet is installed"
else
    echo "❌ phpoffice/phpspreadsheet is NOT installed"
    echo "   Run: composer require phpoffice/phpspreadsheet"
fi

composer show smalot/pdfparser > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ smalot/pdfparser is installed"
else
    echo "❌ smalot/pdfparser is NOT installed"
    echo "   Run: composer require smalot/pdfparser"
fi

echo ""
echo "📂 Checking storage directories..."
if [ -d "storage/app" ]; then
    echo "✅ storage/app directory exists"
else
    echo "⚠️  Creating storage/app directory..."
    mkdir -p storage/app
fi

echo ""
echo "🧪 Checking sample files..."
for file in "sample_upload.csv" "sample_upload.json" "sample_upload.txt"; do
    if [ -f "storage/app/$file" ]; then
        echo "✅ $file exists"
    else
        echo "⚠️  $file not found"
    fi
done

echo ""
echo "📝 Checking component files..."
if [ -f "resources/js/components/BulkUploadComponent.vue" ]; then
    echo "✅ BulkUploadComponent.vue exists"
else
    echo "❌ BulkUploadComponent.vue not found"
fi

echo ""
echo "🔧 Checking configuration..."

# Check upload limits in php.ini
echo "PHP Upload Settings:"
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;"

echo ""
echo "💡 Recommended PHP settings for bulk upload:"
echo "   upload_max_filesize = 10M or higher"
echo "   post_max_size = 10M or higher"
echo "   memory_limit = 256M or higher"

echo ""
echo "🔗 API Routes:"
php artisan route:list --path=api/data/bulk-upload 2>/dev/null
if [ $? -ne 0 ]; then
    echo "⚠️  Could not list routes. Make sure Laravel is properly configured."
fi

echo ""
echo "📊 Database Status:"
php artisan migrate:status 2>/dev/null | grep documents
if [ $? -ne 0 ]; then
    echo "⚠️  Could not check migration status"
fi

echo ""
echo "✨ Setup Summary"
echo "=================="
echo ""
echo "Next steps:"
echo "1. Start the Laravel development server:"
echo "   php artisan serve"
echo ""
echo "2. Start the Vite dev server (in another terminal):"
echo "   npm run dev"
echo ""
echo "3. Open your browser and navigate to:"
echo "   http://localhost:8000"
echo ""
echo "4. Click on the '📤 Bulk Upload' tab"
echo ""
echo "5. Test with the sample files in storage/app/"
echo ""
echo "For more information, see:"
echo "- BULK_UPLOAD_QUICKSTART.md"
echo "- BULK_UPLOAD_GUIDE.md"
echo "- IMPLEMENTATION_SUMMARY.md"
echo ""
echo "✅ Setup check complete!"
