# Bulk Upload Feature - Quick Start Guide

## 🚀 Getting Started

You've successfully added the bulk upload feature to your AI Search system! Here's what was added:

### New Components

1. **Frontend Component**: `resources/js/components/BulkUploadComponent.vue`
   - Beautiful drag-and-drop interface
   - File preview before upload
   - Progress indication
   - Success/error notifications
   - Format guidelines

2. **Backend Controller**: `app/Http/Controllers/DataController.php`
   - New `bulkUpload()` method
   - Support for 6 file formats
   - Robust error handling
   - File parsing logic for each format

3. **New Tab**: Dashboard now includes a "📤 Bulk Upload" tab

4. **API Route**: `POST /api/data/bulk-upload`

## 📦 Installation Completed

The following PHP packages were installed:
- ✅ `phpoffice/phpspreadsheet` (v5.2.0) - For Excel parsing
- ✅ `smalot/pdfparser` (v2.12.1) - For PDF text extraction

## 🎯 How to Use

### Method 1: Via Web Interface

1. **Start your development server** (if not already running):
   ```bash
   cd www
   php artisan serve
   npm run dev  # In another terminal
   ```

2. **Access the dashboard**:
   Open http://localhost:8000 in your browser

3. **Navigate to Bulk Upload**:
   Click the "📤 Bulk Upload" tab

4. **Upload a file**:
   - Drag and drop OR
   - Click "Browse Files"
   - Click "Upload & Process"

### Method 2: Via API

```bash
# CSV example
curl -X POST http://localhost:8000/api/data/bulk-upload \
  -F "file=@storage/app/sample_upload.csv"

# JSON example
curl -X POST http://localhost:8000/api/data/bulk-upload \
  -F "file=@storage/app/sample_upload.json"

# TXT example
curl -X POST http://localhost:8000/api/data/bulk-upload \
  -F "file=@storage/app/sample_upload.txt"
```

## 📝 Sample Files

Three sample files are included in `storage/app/`:

1. **sample_upload.csv** - 5 AI-related articles
2. **sample_upload.json** - 3 technology articles  
3. **sample_upload.txt** - Cloud computing article

Use these to test the functionality!

## 🧪 Testing

Run the automated test script:

```bash
cd www
./test_bulk_upload.sh
```

This will:
- Upload all sample files
- Show API responses
- Display document count statistics

## 📋 Supported Formats

| Format | Extension | Required Fields | Notes |
|--------|-----------|----------------|-------|
| CSV | `.csv` | title, body | Optional: tags, metadata |
| TSV | `.tsv` | title, body | Tab-separated |
| Excel | `.xlsx`, `.xls` | title, body | First sheet only |
| JSON | `.json` | title, body | Array of objects |
| Text | `.txt` | none | Filename → title, content → body |
| PDF | `.pdf` | none | Filename → title, extracted text → body |

## 🔧 Configuration

### File Size Limit
Default: **10 MB**

To change, edit `app/Http/Controllers/DataController.php`:
```php
'file' => 'required|file|mimes:csv,txt,xlsx,xls,pdf,json,tsv|max:10240'
                                                                    ^^^^
                                                                    Change this (in KB)
```

### Allowed MIME Types
To add more file types, update the validation rule and add a parser method.

## 🐛 Troubleshooting

### Issue: "Excel parsing requires PhpSpreadsheet"
**Solution**: Package should already be installed. If not:
```bash
composer require phpoffice/phpspreadsheet
```

### Issue: "PDF parsing requires pdfparser"
**Solution**: Package should already be installed. If not:
```bash
composer require smalot/pdfparser
```

### Issue: Upload button not working
**Solution**: Ensure CSRF token is valid and API routes are accessible

### Issue: File too large
**Solution**: 
1. Check PHP settings: `upload_max_filesize` and `post_max_size`
2. Check Laravel validation limit in controller

## 📊 CSV Format Example

```csv
title,body,tags,metadata
"AI Overview","Artificial Intelligence description","ai,tech","{\"year\": 2024}"
"ML Basics","Machine Learning content","ml,data","{\"difficulty\": \"beginner\"}"
```

## 🎨 JSON Format Example

```json
[
  {
    "title": "Document Title",
    "body": "Document content here",
    "tags": ["tag1", "tag2"],
    "metadata": {
      "author": "John Doe",
      "category": "tech"
    }
  }
]
```

## 🔐 Security Considerations

The implementation includes:
- ✅ File type validation
- ✅ File size limits (10MB)
- ✅ MIME type checking
- ✅ Malformed data handling
- ✅ Exception handling

## 📈 Next Steps

1. **Test with your own data**:
   - Create a CSV with your documents
   - Use the web interface to upload
   - Verify in the Documents tab

2. **Customize for your needs**:
   - Adjust file size limits
   - Add custom metadata fields
   - Implement additional file formats

3. **Monitor performance**:
   - Check upload times for large files
   - Monitor memory usage
   - Consider batch processing for very large datasets

## 📚 Additional Resources

- Full documentation: `BULK_UPLOAD_GUIDE.md`
- Component code: `resources/js/components/BulkUploadComponent.vue`
- Backend code: `app/Http/Controllers/DataController.php`
- API routes: `routes/api.php`

## 🎉 Success!

Your bulk upload feature is now ready to use. The system can now:
- ✅ Import CSV files with multiple documents
- ✅ Parse Excel spreadsheets
- ✅ Extract text from PDFs
- ✅ Process JSON arrays
- ✅ Handle plain text files
- ✅ Support TSV files

Happy uploading! 🚀
