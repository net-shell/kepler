# Folder Tree Feature Documentation

## Overview

The document management system now supports hierarchical folder structures with full path support. Documents are stored with a path field (e.g., `/projects/web/readme.md`) in the database, and the frontend provides a visual tree structure for easy navigation and organization.

## Features

### 1. **Full Path Support**
- Each document has a `path` field stored as a string in the database (max 1000 characters)
- Paths follow Unix-style format: `/folder/subfolder/filename`
- Default path is `/` for root-level documents
- Indexed for fast queries

### 2. **Folder Tree View**
- Visual tree structure showing folders and files
- Expandable/collapsable folders
- Toggle between tree view and traditional list view
- Direct document selection from tree
- Selected document details displayed in side panel

### 3. **Drag & Drop File Management**
- Drag files between folders
- Visual feedback during drag operations
- Automatic path updates on drop

### 4. **Folder Creation**
- Create new folders using the "New Folder" button
- Folders are created with a placeholder `.folder` document
- Folder paths support unlimited nesting

### 5. **Document Creation with Paths**
- Specify folder path when creating documents
- Or provide full custom path directly
- Auto-generated path from folder + filename if not specified
- Real-time path preview

### 6. **Bulk Upload Path Support**
- CSV/Excel files can include a `path` column
- Default path `/{filename}` for files without path
- Maintains folder structure from import data

## API Endpoints

### New Endpoints

#### Get Folder Tree
```
GET /api/data/folder-tree
```
Returns hierarchical tree structure of all documents.

**Response:**
```json
{
  "success": true,
  "tree": [
    {
      "path": "/projects",
      "name": "projects",
      "type": "folder",
      "children": [
        {
          "path": "/projects/readme.md",
          "name": "readme.md",
          "type": "file",
          "id": 1,
          "document": { /* full document object */ }
        }
      ]
    }
  ]
}
```

#### Get Documents by Folder
```
GET /api/data/by-folder?folder=/path/to/folder&recursive=true
```
Returns documents in a specific folder.

**Query Parameters:**
- `folder` (string): Folder path (default: `/`)
- `recursive` (boolean): Include subfolders (default: false)

**Response:**
```json
{
  "success": true,
  "documents": [ /* array of documents */ ],
  "folder": "/path/to/folder"
}
```

#### Move Document
```
POST /api/data/{id}/move
```
Move a document to a new path.

**Request Body:**
```json
{
  "new_path": "/new/path/to/file"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Document moved successfully",
  "document": { /* updated document */ }
}
```

### Updated Endpoints

#### Create Document
```
POST /api/data
```
Now accepts optional `path` field.

**Request Body:**
```json
{
  "title": "My Document",
  "path": "/projects/web/document.md",
  "body": "Document content",
  "tags": ["tag1", "tag2"],
  "metadata": {}
}
```

If `path` is not provided, it defaults to `/{title}`.

#### Update Document
```
PUT /api/data/{id}
```
Now accepts optional `path` field for moving/renaming.

## Database Changes

### Migration: `add_path_to_documents_table`

Added:
- `path` column (VARCHAR 1000, default '/')
- Index on `path` for fast querying

## Model Updates

### Document Model

**New Fields:**
- `path` - Full path to document

**New Methods:**
- `getFolderPath()` - Returns parent folder path
- `getFileName()` - Returns filename from path
- `scopeInFolder($query, $folder)` - Query documents in folder
- `scopeDirectChildren($query, $folder)` - Query only direct children

## Frontend Components

### 1. **FolderTree.vue**
Reusable tree component with:
- Recursive folder rendering
- Expand/collapse functionality
- Drag & drop support
- File/folder icons
- Selection highlighting
- Delete button per file

**Props:**
- `nodes` - Array of FolderTreeNode objects
- `currentPath` - Currently selected path

**Events:**
- `selectFolder` - Folder clicked
- `selectDocument` - Document clicked
- `deleteDocument` - Delete button clicked
- `moveDocument` - Document dropped on folder

### 2. **DocumentList.vue**
Updated with:
- Tree view / List view toggle
- Folder tree integration
- Document details side panel
- Create folder functionality
- Move document support

**View Modes:**
- **Tree View**: Split view with folder tree on left, document details on right
- **List View**: Traditional flat list showing full paths

### 3. **DataFeedComponent.vue**
Enhanced with:
- Folder path input
- Full path input (optional)
- Real-time path preview
- Automatic path generation

## TypeScript Types

### New Interfaces

```typescript
export interface FolderTreeNode {
  path: string;
  name: string;
  type: 'file' | 'folder';
  id?: number;
  document?: Document;
  children?: FolderTreeNode[];
}

export interface FolderTreeResponse {
  success: boolean;
  tree: FolderTreeNode[];
  error?: string;
}

export interface MoveDocumentRequest {
  new_path: string;
}

export interface FolderDocumentsResponse {
  success: boolean;
  documents: Document[];
  folder: string;
  error?: string;
}
```

### Updated Interfaces

```typescript
export interface Document {
  id: number;
  title: string;
  path?: string;  // NEW
  body: string;
  tags: string[] | null;
  metadata: Record<string, any> | null;
  tfidf_score: number | null;
  created_at: string;
  updated_at: string;
}

export interface DocumentFormData {
  title: string;
  path?: string;  // NEW
  body: string;
  tags: string[];
  metadata: Record<string, any>;
}
```

## Usage Examples

### Creating a Document with Path

```javascript
const response = await fetch('/api/data', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    title: 'README.md',
    path: '/projects/web/README.md',
    body: 'Project documentation',
    tags: ['documentation'],
    metadata: {}
  }),
});
```

### Moving a Document

```javascript
const response = await fetch('/api/data/123/move', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    new_path: '/archive/old-readme.md'
  }),
});
```

### Getting Folder Tree

```javascript
const response = await fetch('/api/data/folder-tree');
const data = await response.json();
// data.tree contains hierarchical structure
```

## Bulk Upload with Paths

### CSV Format
Your CSV can now include a `path` column:

```csv
title,body,tags,path
My Document,Content here,"tag1,tag2",/folder/document.md
Another Doc,More content,"tag3",/folder/subfolder/file.md
```

If `path` is omitted, documents go to `/{title}` by default.

### JSON Format
```json
[
  {
    "title": "Document 1",
    "path": "/projects/doc1.md",
    "body": "Content",
    "tags": ["tag1"],
    "metadata": {}
  },
  {
    "title": "Document 2",
    "path": "/projects/subfolder/doc2.md",
    "body": "More content",
    "tags": ["tag2"],
    "metadata": {}
  }
]
```

## Best Practices

1. **Path Naming**
   - Use lowercase for consistency
   - Use hyphens instead of spaces: `/my-folder/my-file.md`
   - Include file extensions when appropriate
   - Keep paths under 1000 characters

2. **Folder Organization**
   - Group related documents in folders
   - Use meaningful folder names
   - Avoid excessive nesting (3-5 levels max recommended)

3. **Moving Documents**
   - Prefer drag & drop in tree view
   - Verify path before moving important documents
   - Keep file extensions when renaming

4. **Creating Folders**
   - Folders are automatically created when documents are added
   - Use "New Folder" button to pre-create folder structure
   - `.folder` placeholder files are hidden in UI but exist in database

## Migration Notes

### For Existing Databases

1. The migration sets default path to `/` for all existing documents
2. To organize existing documents:
   - Update paths manually via API
   - Or use the drag & drop feature in the UI
   - Batch update via database if needed:

   ```sql
   UPDATE documents
   SET path = CONCAT('/', title)
   WHERE path = '/';
   ```

### Backwards Compatibility

- All existing endpoints continue to work
- `path` field is optional in requests
- Documents without explicit path use `/{title}` as default
- List view shows documents as before, now with paths visible

## Performance Considerations

- Path field is indexed for fast queries
- Tree building is done server-side for large datasets
- Folder tree is cached in frontend state
- Lazy loading can be implemented for very large folder structures (future enhancement)

## Future Enhancements

Potential additions:
- Folder permissions/access control
- Shared folders
- Folder metadata
- Breadcrumb navigation
- Search within folder
- Bulk move operations
- Path validation and sanitization
- Virtual folders (tags as folders)
- Folder color coding
