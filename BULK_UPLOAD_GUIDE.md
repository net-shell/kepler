# Bulk Upload Feature

## Overview
The bulk upload feature allows you to import multiple documents into the system from various file formats. This is useful for:
- Migrating existing data
- Importing large datasets
- Batch processing documents
- Loading initial demo data

## Supported File Formats

### 1. CSV (Comma-Separated Values)
- **Required columns**: `title`, `body`
- **Optional columns**: `tags`, `metadata`
- **Format for tags**: Comma-separated string (e.g., "ai,machine learning,tech")
- **Format for metadata**: JSON string (e.g., '{"year": 2024, "category": "tech"}')

**Example CSV:**
```csv
title,body,tags,metadata
My Document,"This is the content","tag1,tag2","{\"category\": \"tech\"}"
```

### 2. TSV (Tab-Separated Values)
- Same structure as CSV but uses tabs instead of commas as delimiter
- Useful when your content contains many commas

### 3. Excel (.xlsx, .xls)
- First sheet will be processed
- Same column requirements as CSV
- Headers should be in the first row

### 4. JSON
- Must be an array of document objects
- Each object must have `title` and `body` fields
- `tags` and `metadata` are optional

**Example JSON:**
```json
[
  {
    "title": "Document Title",
    "body": "Document content goes here",
    "tags": ["tag1", "tag2"],
    "metadata": {
      "author": "John Doe",
      "year": 2024
    }
  }
]
```

### 5. Plain Text (.txt)
- Entire file content becomes the document body
- Filename (without extension) is used as the title
- Automatically tagged with "text" and "imported"

### 6. PDF
- Text is extracted from the PDF
- Filename is used as the title
- Automatically tagged with "pdf" and "imported"
- **Note**: Only text-based PDFs are supported; scanned images won't be extracted

## Usage

### Via Web Interface
1. Navigate to the Dashboard
2. Click on the "📤 Bulk Upload" tab
3. Either drag and drop your file or click "Browse Files"
4. Click "Upload & Process"
5. Wait for the upload to complete

### Via API
```bash
curl -X POST http://localhost:8000/api/data/bulk-upload \
  -H "Content-Type: multipart/form-data" \
  -F "file=@/path/to/your/file.csv"
```

**Response:**
```json
{
  "success": true,
  "message": "5 documents uploaded successfully",
  "count": 5
}
```

## File Size Limits
- Maximum file size: 10 MB
- For larger files, consider splitting them into smaller chunks

## Error Handling

### Common Errors

**"CSV must contain 'title' and 'body' columns"**
- Ensure your CSV/Excel file has headers named exactly `title` and `body`

**"No valid documents found in file"**
- Check that your file has data rows (not just headers)
- Ensure title and body fields are not empty

**"Invalid JSON format"**
- Validate your JSON using a JSON validator
- Ensure proper escaping of quotes and special characters

**"Could not extract text from PDF"**
- The PDF might be image-based (scanned)
- Try converting the PDF to text manually first

## Sample Files

Sample files are available in `storage/app/` for testing:
- `sample_upload.csv` - CSV format with 5 AI-related articles
- `sample_upload.json` - JSON format with 3 technology articles
- `sample_upload.txt` - Plain text article about cloud computing

## Backend Implementation

The bulk upload feature is implemented in:
- **Controller**: `app/Http/Controllers/DataController.php`
- **Route**: `POST /api/data/bulk-upload`
- **Component**: `resources/js/components/BulkUploadComponent.vue`

### Required PHP Packages
- `phpoffice/phpspreadsheet` - For Excel file parsing
- `smalot/pdfparser` - For PDF text extraction

Install these packages:
```bash
composer require phpoffice/phpspreadsheet smalot/pdfparser
```

## Best Practices

1. **CSV/Excel Files**
   - Always include a header row
   - Use UTF-8 encoding
   - Enclose fields containing commas in quotes
   - Escape internal quotes with double quotes

2. **JSON Files**
   - Validate JSON before uploading
   - Use proper encoding for special characters
   - Keep file structure consistent

3. **Large Datasets**
   - Split into smaller files (< 10MB each)
   - Upload during off-peak hours
   - Consider batch API calls for programmatic uploads

4. **Data Quality**
   - Clean data before uploading
   - Ensure all required fields are present
   - Use consistent tag naming conventions
   - Validate metadata structure

## Troubleshooting

### Upload Fails with 413 Payload Too Large
Increase the maximum upload size in your web server configuration and PHP settings.

### Memory Issues with Large Excel Files
For very large Excel files, consider:
- Converting to CSV first
- Splitting into multiple files
- Increasing PHP memory limit

### Character Encoding Issues
- Always use UTF-8 encoding
- Convert files before uploading if using different encoding

## API Reference

### Bulk Upload Endpoint

**Endpoint**: `POST /api/data/bulk-upload`

**Headers**:
```
Content-Type: multipart/form-data
X-Requested-With: XMLHttpRequest
```

**Parameters**:
- `file` (required): The file to upload

**Supported MIME Types**:
- text/csv
- text/plain
- application/json
- application/pdf
- application/vnd.ms-excel
- application/vnd.openxmlformats-officedocument.spreadsheetml.sheet

**Success Response** (200):
```json
{
  "success": true,
  "message": "10 documents uploaded successfully",
  "count": 10
}
```

**Error Response** (400/500):
```json
{
  "success": false,
  "error": "Error message here"
}
```

## Future Enhancements

Potential improvements for future versions:
- Support for Word documents (.docx)
- Support for Markdown files
- Real-time progress updates for large files
- Preview before importing
- Duplicate detection
- URL-based import (import from web)
- Scheduled imports
- Import templates
