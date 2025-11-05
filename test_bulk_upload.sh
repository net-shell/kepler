#!/bin/bash

# Test script for bulk upload functionality

echo "🧪 Testing Bulk Upload Functionality"
echo "====================================="
echo ""

BASE_URL="http://localhost:8000"
API_URL="${BASE_URL}/api/data/bulk-upload"

# Test CSV upload
echo "📊 Test 1: CSV Upload"
echo "---------------------"
if [ -f "storage/app/sample_upload.csv" ]; then
    curl -X POST "${API_URL}" \
         -H "X-Requested-With: XMLHttpRequest" \
         -F "file=@storage/app/sample_upload.csv" \
         -w "\nHTTP Status: %{http_code}\n"
    echo -e "\n"
else
    echo "❌ Sample CSV file not found"
fi

# Test JSON upload
echo "📋 Test 2: JSON Upload"
echo "----------------------"
if [ -f "storage/app/sample_upload.json" ]; then
    curl -X POST "${API_URL}" \
         -H "X-Requested-With: XMLHttpRequest" \
         -F "file=@storage/app/sample_upload.json" \
         -w "\nHTTP Status: %{http_code}\n"
    echo -e "\n"
else
    echo "❌ Sample JSON file not found"
fi

# Test TXT upload
echo "📄 Test 3: Text Upload"
echo "----------------------"
if [ -f "storage/app/sample_upload.txt" ]; then
    curl -X POST "${API_URL}" \
         -H "X-Requested-With: XMLHttpRequest" \
         -F "file=@storage/app/sample_upload.txt" \
         -w "\nHTTP Status: %{http_code}\n"
    echo -e "\n"
else
    echo "❌ Sample TXT file not found"
fi

# Check total documents
echo "📈 Checking Total Documents"
echo "---------------------------"
curl -s "${BASE_URL}/api/search/stats" | python3 -m json.tool
echo -e "\n"

echo "✅ Tests completed!"
