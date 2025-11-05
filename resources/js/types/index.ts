export interface Document {
    id: number;
    title: string;
    path?: string;
    body?: string; // Optional - excluded from list views
    tags: string[] | null;
    metadata?: Record<string, any> | null;
    tfidf_score?: number | null;
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
}

export interface SearchResult {
    score: number;
    record: Document;
    rank: number;
}

export interface SearchResponse {
    success: boolean;
    results: SearchResult[];
    query: string;
    error?: string;
}

export interface DataResponse {
    success: boolean;
    message?: string;
    document?: Document;
    documents?: Document[];
    error?: string;
}

export interface PaginatedResponse {
    current_page: number;
    data: Document[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

export interface StatsResponse {
    total_documents: number;
    latest_document: Document | null;
    oldest_document: Document | null;
}

export interface DocumentFormData {
    title: string;
    path?: string;
    body: string;
    tags: string[];
    metadata: Record<string, any>;
}

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
