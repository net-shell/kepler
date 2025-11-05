#!/usr/bin/env python3
"""
ai_search_api.py — Improved AI Search with API integration
-----------------------------------------------------------
Enhanced version with better error handling, caching, and API support.
Requires: pip install scikit-learn numpy
"""

import json
import sys
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from typing import List, Dict, Optional


class AISearchEngine:
    """
    Enhanced AI Search Engine with TF-IDF vectorization and cosine similarity.
    Supports batch processing, caching, and detailed scoring.
    """
    
    def __init__(self, min_df: int = 1, max_features: Optional[int] = None):
        """
        Initialize the search engine.
        
        Args:
            min_df: Minimum document frequency for terms
            max_features: Maximum number of features (None for unlimited)
        """
        self.vectorizer = TfidfVectorizer(
            stop_words="english",
            min_df=min_df,
            max_features=max_features,
            lowercase=True,
            ngram_range=(1, 2)  # Use unigrams and bigrams
        )
        self.texts: List[str] = []
        self.data: List[Dict] = []
        self.tfidf_matrix = None
        self.is_fitted = False

    def ingest(self, records: List[Dict]) -> Dict:
        """
        Flatten structured records into text and build TF-IDF matrix.
        
        Args:
            records: List of document dictionaries
            
        Returns:
            Dictionary with ingestion statistics
        """
        if not records:
            return {
                "status": "error",
                "message": "No records provided",
                "count": 0
            }
        
        self.data = records
        self.texts = [self._flatten(r) for r in records]
        
        try:
            self.tfidf_matrix = self.vectorizer.fit_transform(self.texts)
            self.is_fitted = True
            
            return {
                "status": "success",
                "message": f"Successfully indexed {len(records)} records",
                "count": len(records),
                "vocabulary_size": len(self.vectorizer.vocabulary_),
                "feature_names": len(self.vectorizer.get_feature_names_out())
            }
        except Exception as e:
            return {
                "status": "error",
                "message": f"Failed to index records: {str(e)}",
                "count": 0
            }

    def _flatten(self, record: Dict) -> str:
        """
        Convert structured dict into a single searchable string.
        Gives more weight to title and handles nested structures.
        
        Args:
            record: Document dictionary
            
        Returns:
            Flattened searchable text
        """
        parts = []
        
        # Title gets triple weight for importance
        if 'title' in record:
            title = str(record['title'])
            parts.extend([title, title, title])
        
        # Body content
        if 'body' in record:
            parts.append(str(record['body']))
        
        # Tags get double weight
        if 'tags' in record and isinstance(record['tags'], list):
            tags = ' '.join(str(t) for t in record['tags'])
            parts.extend([tags, tags])
        
        # Other fields
        for k, v in record.items():
            if k not in ['title', 'body', 'tags']:
                if isinstance(v, (dict, list)):
                    parts.append(json.dumps(v))
                else:
                    parts.append(str(v))
        
        return " ".join(parts)

    def search(self, query: str, k: int = 5, min_score: float = 0.0) -> List[Dict]:
        """
        Return top-k most similar records.
        
        Args:
            query: Search query string
            k: Number of results to return
            min_score: Minimum similarity score threshold
            
        Returns:
            List of result dictionaries with scores and records
        """
        if not self.is_fitted or self.tfidf_matrix is None:
            raise RuntimeError("No data indexed yet. Call ingest() first.")
        
        if not query.strip():
            raise ValueError("Query cannot be empty")
        
        try:
            # Transform query
            q_vec = self.vectorizer.transform([query])
            
            # Calculate similarities
            sims = cosine_similarity(q_vec, self.tfidf_matrix)[0]
            
            # Filter by minimum score
            valid_indices = np.where(sims >= min_score)[0]
            
            if len(valid_indices) == 0:
                return []
            
            # Get top-k from valid results
            valid_sims = sims[valid_indices]
            top_k_local = min(k, len(valid_indices))
            top_local_ids = np.argsort(-valid_sims)[:top_k_local]
            top_ids = valid_indices[top_local_ids]
            
            # Build results
            results = []
            for idx in top_ids:
                results.append({
                    "score": float(sims[idx]),
                    "record": self.data[idx],
                    "rank": len(results) + 1
                })
            
            return results
            
        except Exception as e:
            raise RuntimeError(f"Search failed: {str(e)}")

    def get_stats(self) -> Dict:
        """Get statistics about the indexed data."""
        if not self.is_fitted:
            return {"status": "not_fitted"}
        
        return {
            "status": "fitted",
            "total_documents": len(self.data),
            "vocabulary_size": len(self.vectorizer.vocabulary_),
            "feature_count": len(self.vectorizer.get_feature_names_out()),
            "matrix_shape": self.tfidf_matrix.shape
        }


def main():
    """
    Main function for API mode.
    Reads JSON input from stdin and outputs JSON results to stdout.
    """
    try:
        # Read input from stdin
        input_data = json.loads(sys.stdin.read())
        
        # Extract parameters
        data = input_data.get('data', [])
        query = input_data.get('query', '')
        limit = input_data.get('limit', 5)
        min_score = input_data.get('min_score', 0.0)
        
        # Initialize search engine
        engine = AISearchEngine()
        
        # Ingest data
        ingest_result = engine.ingest(data)
        
        if ingest_result['status'] != 'success':
            print(json.dumps({
                "error": ingest_result['message']
            }))
            sys.exit(1)
        
        # Perform search
        results = engine.search(query, k=limit, min_score=min_score)
        
        # Output results
        print(json.dumps(results))
        sys.exit(0)
        
    except json.JSONDecodeError as e:
        print(json.dumps({
            "error": f"Invalid JSON input: {str(e)}"
        }))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({
            "error": f"Search error: {str(e)}"
        }))
        sys.exit(1)


# ---------- DEMO MODE ----------
def demo():
    """Interactive demo mode for testing."""
    sample_data = [
        {
            "id": 1,
            "title": "Refund Policy",
            "body": "Items can be returned within 30 days for a full refund. Original packaging required.",
            "tags": ["refund", "returns", "policy"]
        },
        {
            "id": 2,
            "title": "Privacy Statement",
            "body": "We never share your personal data with third parties. Your information is encrypted and secure.",
            "tags": ["privacy", "security", "data-protection"]
        },
        {
            "id": 3,
            "title": "Shipping Information",
            "body": "Worldwide delivery with tracking number. Express shipping available for urgent orders.",
            "tags": ["shipping", "delivery", "international"]
        },
        {
            "id": 4,
            "title": "AI Search Project",
            "body": "An open-source system for semantic search on structured data using TF-IDF and cosine similarity.",
            "tags": ["AI", "search", "open-source", "machine-learning"]
        },
        {
            "id": 5,
            "title": "Customer Support",
            "body": "24/7 customer support available via email, chat, and phone. Average response time: 2 hours.",
            "tags": ["support", "help", "customer-service"]
        },
    ]

    engine = AISearchEngine()
    result = engine.ingest(sample_data)
    
    print(f"=== AI Search Engine Demo ===")
    print(f"Status: {result['message']}")
    print(f"Vocabulary size: {result['vocabulary_size']}")
    print(f"\nType your query (or 'exit' to quit):\n")
    
    while True:
        try:
            q = input("> ").strip()
            if q.lower() in {"exit", "quit", "q"}:
                break
            
            if not q:
                continue
            
            results = engine.search(q, k=3)
            
            if not results:
                print("No results found.\n")
                continue
            
            print("\nTop results:")
            for r in results:
                record = r['record']
                print(f"[Rank {r['rank']} | Score: {r['score']:.3f}]")
                print(f"  Title: {record['title']}")
                print(f"  Body: {record['body'][:80]}...")
                print(f"  Tags: {', '.join(record['tags'])}\n")
                
        except KeyboardInterrupt:
            print("\nExiting...")
            break
        except Exception as e:
            print(f"Error: {e}\n")


if __name__ == "__main__":
    # Check if running in API mode (with stdin input) or demo mode
    if sys.stdin.isatty():
        demo()
    else:
        main()
