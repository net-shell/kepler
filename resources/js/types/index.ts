export interface Document {
  id: number;
  title: string;
  body: string;
  tags: string[] | null;
  metadata: Record<string, any> | null;
  tfidf_score: number | null;
  created_at: string;
  updated_at: string;
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
  body: string;
  tags: string[];
  metadata: Record<string, any>;
}
