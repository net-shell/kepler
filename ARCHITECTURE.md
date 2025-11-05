# Bulk Upload Feature - Architecture & Data Flow

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                          Frontend (Vue 3)                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Dashboard.vue                                                   │
│  ┌──────────┬──────────┬──────────────┬────────────┐           │
│  │  Search  │ Add Data │ Bulk Upload  │ Documents  │           │
│  └──────────┴──────────┴──────────────┴────────────┘           │
│                             │                                    │
│                             ▼                                    │
│                   BulkUploadComponent.vue                        │
│                   ┌────────────────────┐                         │
│                   │ Drag & Drop Zone   │                         │
│                   │ File Selection     │                         │
│                   │ Upload Button      │                         │
│                   │ Progress Display   │                         │
│                   └────────────────────┘                         │
│                             │                                    │
└─────────────────────────────┼────────────────────────────────────┘
                              │
                              ▼ FormData (multipart/form-data)
                              │
┌─────────────────────────────┼────────────────────────────────────┐
│                          API Layer                                │
├─────────────────────────────┼────────────────────────────────────┤
│                             ▼                                     │
│              POST /api/data/bulk-upload                           │
│                             │                                     │
└─────────────────────────────┼─────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────┼─────────────────────────────────────┐
│                       Backend (Laravel)                           │
├─────────────────────────────┼─────────────────────────────────────┤
│                             ▼                                     │
│              DataController::bulkUpload()                         │
│                             │                                     │
│                             ▼                                     │
│              ┌──────────────────────────┐                        │
│              │   File Validation        │                        │
│              │   - Type check (MIME)    │                        │
│              │   - Size check (10MB)    │                        │
│              │   - Extension check      │                        │
│              └──────────────┬───────────┘                        │
│                             │                                     │
│                             ▼                                     │
│              ┌──────────────────────────┐                        │
│              │   Format Detection       │                        │
│              │   - CSV/TSV              │                        │
│              │   - Excel (.xlsx/.xls)   │                        │
│              │   - JSON                 │                        │
│              │   - PDF                  │                        │
│              │   - TXT                  │                        │
│              └──────────────┬───────────┘                        │
│                             │                                     │
│              ┌──────────────┴───────────┐                        │
│              ▼                          ▼                         │
│    ┌─────────────────┐      ┌──────────────────┐                │
│    │   parseCSV()    │      │  parseExcel()    │                │
│    │   parseTSV()    │      │  using           │                │
│    │   using PHP     │      │  PhpSpreadsheet  │                │
│    │   fgetcsv()     │      └────────┬─────────┘                │
│    └────────┬────────┘               │                           │
│             │                         │                           │
│             ▼                         ▼                           │
│    ┌─────────────────┐      ┌──────────────────┐                │
│    │   parseJSON()   │      │   parsePDF()     │                │
│    │   using PHP     │      │   using          │                │
│    │   json_decode() │      │   Smalot/Parser  │                │
│    └────────┬────────┘      └────────┬─────────┘                │
│             │                         │                           │
│             ▼                         ▼                           │
│    ┌─────────────────┐      ┌──────────────────┐                │
│    │   parseText()   │      │   All parsers    │                │
│    │   using PHP     │      │   return:        │                │
│    │   file_get...() │      │   Array<Doc>     │                │
│    └────────┬────────┘      └────────┬─────────┘                │
│             │                         │                           │
│             └─────────┬───────────────┘                           │
│                       ▼                                           │
│           ┌───────────────────────┐                              │
│           │  Document Validation  │                              │
│           │  - Required fields    │                              │
│           │  - Data sanitization  │                              │
│           │  - Type casting       │                              │
│           └───────────┬───────────┘                              │
│                       │                                           │
│                       ▼                                           │
│           ┌───────────────────────┐                              │
│           │   Batch Creation      │                              │
│           │   Document::create()  │                              │
│           │   for each document   │                              │
│           └───────────┬───────────┘                              │
│                       │                                           │
└───────────────────────┼───────────────────────────────────────────┘
                        │
                        ▼
┌───────────────────────┼───────────────────────────────────────────┐
│                   Database (MySQL)                                │
├───────────────────────┼───────────────────────────────────────────┤
│                       ▼                                           │
│           ┌───────────────────────┐                              │
│           │   documents table     │                              │
│           ├───────────────────────┤                              │
│           │ id                    │                              │
│           │ title                 │                              │
│           │ body                  │                              │
│           │ tags (JSON)           │                              │
│           │ metadata (JSON)       │                              │
│           │ tfidf_score           │                              │
│           │ created_at            │                              │
│           │ updated_at            │                              │
│           └───────────┬───────────┘                              │
│                       │                                           │
└───────────────────────┼───────────────────────────────────────────┘
                        │
                        ▼
                   ┌─────────┐
                   │ Success │
                   └─────────┘
```

## Data Flow Diagram

```
User Action                  Processing                    Storage
─────────────               ──────────────                ─────────

┌──────────┐                                              
│ Select   │                                              
│ File     │                                              
└────┬─────┘                                              
     │                                                     
     ▼                                                     
┌──────────┐                                              
│ Preview  │                                              
│ File     │                                              
└────┬─────┘                                              
     │                                                     
     ▼                                                     
┌──────────┐   FormData    ┌─────────────┐              
│ Click    │──────────────▶│ API Request │              
│ Upload   │               └──────┬──────┘              
└──────────┘                      │                       
                                  ▼                       
                          ┌───────────────┐               
                          │ Validate File │               
                          │ - Type        │               
                          │ - Size        │               
                          │ - Extension   │               
                          └───────┬───────┘               
                                  │                       
                         ┌────────┴────────┐              
                         │                 │              
                    Accept?              Reject           
                         │                 │              
                       Yes                 ▼              
                         │          ┌─────────────┐       
                         │          │ Return      │       
                         │          │ Error 400   │       
                         │          └─────────────┘       
                         ▼                                
                  ┌─────────────┐                         
                  │ Detect      │                         
                  │ Format      │                         
                  └──────┬──────┘                         
                         │                                
        ┌────────────────┼────────────────┐              
        ▼                ▼                ▼              
   ┌────────┐      ┌─────────┐     ┌─────────┐          
   │ CSV/   │      │ Excel/  │     │ PDF/    │          
   │ TSV    │      │ XLSX    │     │ TXT/    │          
   │ Parser │      │ Parser  │     │ JSON    │          
   └───┬────┘      └────┬────┘     └────┬────┘          
       │                │               │               
       └────────────────┼───────────────┘               
                        ▼                               
                 ┌──────────────┐                       
                 │ Extract Data │                       
                 │ Array<{      │                       
                 │  title,      │                       
                 │  body,       │                       
                 │  tags,       │                       
                 │  metadata    │                       
                 │ }>           │                       
                 └──────┬───────┘                       
                        │                               
                        ▼                               
                 ┌──────────────┐                       
                 │ Validate     │                       
                 │ Each Doc     │                       
                 └──────┬───────┘                       
                        │                               
                        ▼                               
                 ┌──────────────┐        ┌──────────┐  
                 │ Create       │──────▶│ documents│  
                 │ Documents    │        │ table    │  
                 │ in DB        │        └──────────┘  
                 └──────┬───────┘                       
                        │                               
                        ▼                               
                 ┌──────────────┐                       
                 │ Return       │                       
                 │ Success +    │                       
                 │ Count        │                       
                 └──────┬───────┘                       
                        │                               
                        ▼                               
User Sees          ┌──────────────┐                       
Result            │ Show Success │                       
                  │ Message      │                       
                  └──────────────┘                       
```

## File Format Processing Flow

### CSV/TSV Flow
```
File → Read Header Row → Map Columns → Read Data Rows → 
Create Array → Validate → Insert DB
```

### Excel Flow
```
File → PhpSpreadsheet Load → Get Active Sheet → 
Convert to Array → Extract Header → Map Columns → 
Create Array → Validate → Insert DB
```

### PDF Flow
```
File → PDF Parser Load → Extract Text → 
Create Single Document → Use Filename as Title → 
Add Auto-Tags → Insert DB
```

### JSON Flow
```
File → Read Content → JSON Decode → 
Validate Structure → Extract Documents → 
Validate Each → Insert DB
```

### Text Flow
```
File → Read Content → Create Single Document → 
Use Filename as Title → Add Auto-Tags → Insert DB
```

## Component Interaction

```
┌─────────────────────────────────────────────────────────┐
│ Dashboard.vue                                           │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ State Management                                    │ │
│ │ - activeTab: 'search' | 'feed' | 'bulk' | 'list'   │ │
│ │ - stats: StatsResponse                              │ │
│ │ - loading: boolean                                   │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Methods                                             │ │
│ │ - loadStats(): Load document statistics            │ │
│ │ - handleDataAdded(): Refresh after upload          │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Child Components                                    │ │
│ │                                                     │ │
│ │ SearchComponent (v-if="activeTab === 'search'")    │ │
│ │ DataFeedComponent (v-else-if="activeTab === 'feed'")│ │
│ │ BulkUploadComponent (v-else-if="activeTab === 'bulk'")│
│ │   @data-uploaded="handleDataAdded"                 │ │
│ │ DocumentList (v-else-if="activeTab === 'list'")    │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────┐
│ BulkUploadComponent.vue                                 │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ State                                               │ │
│ │ - file: File | null                                 │ │
│ │ - uploading: boolean                                │ │
│ │ - uploadProgress: number                            │ │
│ │ - uploadResult: { success, message, count } | null  │ │
│ │ - dragActive: boolean                               │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Methods                                             │ │
│ │ - handleFileChange(): Process file selection       │ │
│ │ - handleDrop(): Process drag & drop                │ │
│ │ - uploadFile(): Send file to API                   │ │
│ │ - clearFile(): Reset state                         │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Events Emitted                                      │ │
│ │ - data-uploaded: When upload succeeds              │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

## Error Handling Flow

```
                    ┌──────────────┐
                    │ File Upload  │
                    └──────┬───────┘
                           │
                           ▼
                    ┌──────────────┐
                    │ Validation   │
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              │                         │
           Success                   Failure
              │                         │
              ▼                         ▼
       ┌──────────────┐         ┌──────────────┐
       │ Parse File   │         │ Return 400   │
       └──────┬───────┘         │ with error   │
              │                 └──────────────┘
              ▼
       ┌──────────────┐
       │ Parse Logic  │
       └──────┬───────┘
              │
    ┌─────────┼─────────┐
    │                   │
Success              Failure
    │                   │
    ▼                   ▼
┌──────────────┐  ┌──────────────┐
│ Create Docs  │  │ Return 400   │
└──────┬───────┘  │ Parse error  │
       │          └──────────────┘
       ▼
┌──────────────┐
│ DB Insert    │
└──────┬───────┘
       │
 ┌─────┼─────┐
 │           │
Success   Failure
 │           │
 ▼           ▼
┌────┐  ┌──────────┐
│200 │  │ 500 DB   │
│OK  │  │ Error    │
└────┘  └──────────┘
```

## Security Layers

```
Layer 1: Frontend Validation
  ├─ File type check (accept attribute)
  ├─ File size preview
  └─ User feedback

Layer 2: HTTP Layer
  ├─ CSRF token (Laravel)
  ├─ Content-Type validation
  └─ Request size limits

Layer 3: Application Layer
  ├─ MIME type validation
  ├─ Extension validation
  ├─ File size limit (10MB)
  └─ Input sanitization

Layer 4: Parser Layer
  ├─ Format validation
  ├─ Structure validation
  ├─ Required fields check
  └─ Data type validation

Layer 5: Database Layer
  ├─ Model validation
  ├─ Type casting
  └─ SQL injection protection
```

## Performance Considerations

```
File Size           Strategy              Notes
─────────           ────────              ─────
< 1 MB             Direct processing      Fast, no issues
1-5 MB             Chunk reading          Optional optimization
5-10 MB            Memory monitoring      Watch for limits
> 10 MB            Rejected               Hard limit (configurable)

For large files:
1. Consider queue processing
2. Implement streaming
3. Add progress tracking
4. Use background jobs
```

## Technology Stack

```
┌─────────────────────────────────────┐
│           Frontend Stack             │
├─────────────────────────────────────┤
│ Vue 3 (Composition API)             │
│ TypeScript                          │
│ Vite (Build tool)                   │
│ Browser File API                    │
│ FormData API                        │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│           Backend Stack              │
├─────────────────────────────────────┤
│ Laravel 10                          │
│ PHP 8.1+                            │
│ phpoffice/phpspreadsheet            │
│ smalot/pdfparser                    │
│ Native PHP CSV/JSON parsers         │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│          Database Stack              │
├─────────────────────────────────────┤
│ MySQL/MariaDB                       │
│ JSON column type                    │
│ Full-text indexing ready            │
└─────────────────────────────────────┘
```

This architecture provides a robust, scalable, and maintainable solution for bulk document uploads.
