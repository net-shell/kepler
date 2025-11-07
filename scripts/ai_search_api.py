#!/usr/bin/env python3
"""
ai_search_api.py — Improved AI Search with API integration
-----------------------------------------------------------
Enhanced version with better error handling, caching, and API support.
Requires: pip install scikit-learn numpy python-dotenv
"""

import json
import sys
import os
import sqlite3
import numpy as np
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from typing import List, Dict, Optional

try:
    from dotenv import load_dotenv
except ImportError:
    load_dotenv = None


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
    Accepts either 'data' array directly or loads from database.
    """
    try:
        # Read input from stdin
        input_data = json.loads(sys.stdin.read())

        # Extract parameters
        query = input_data.get('query', '')
        limit = input_data.get('limit', 5)
        min_score = input_data.get('min_score', 0.0)

        # Initialize search engine
        engine = AISearchEngine()

        # Check if data is provided directly in input
        if 'data' in input_data and input_data['data']:
            # Use provided data
            data = input_data['data']
        else:
            # Fall back to loading from database
            db_path = get_db_path()
            data = load_documents_from_db(db_path)

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
    except FileNotFoundError as e:
        print(json.dumps({
            "error": f"Database error: {str(e)}"
        }))
        sys.exit(1)
    except sqlite3.Error as e:
        print(json.dumps({
            "error": f"Database error: {str(e)}"
        }))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({
            "error": f"Search error: {str(e)}"
        }))
        sys.exit(1)


# ---------- INTERACTIVE MODE ----------
def load_documents_from_db(db_path: str) -> List[Dict]:
    """
    Load documents from SQLite database.

    Args:
        db_path: Path to the SQLite database file

    Returns:
        List of document dictionaries
    """
    if not os.path.exists(db_path):
        raise FileNotFoundError(f"Database file not found: {db_path}")

    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row  # Enable column access by name
    cursor = conn.cursor()

    try:
        cursor.execute("""
            SELECT id, title, body, tags, metadata, created_at, updated_at
            FROM documents
            ORDER BY id
        """)

        rows = cursor.fetchall()
        documents = []

        for row in rows:
            doc = {
                'id': row['id'],
                'title': row['title'],
                'body': row['body'],
            }

            # Parse JSON fields
            if row['tags']:
                try:
                    doc['tags'] = json.loads(row['tags'])
                except (json.JSONDecodeError, TypeError):
                    doc['tags'] = []
            else:
                doc['tags'] = []

            if row['metadata']:
                try:
                    doc['metadata'] = json.loads(row['metadata'])
                except (json.JSONDecodeError, TypeError):
                    doc['metadata'] = {}
            else:
                doc['metadata'] = {}

            documents.append(doc)

        return documents

    except sqlite3.Error as e:
        print(f"Database error: {e}")
        raise
    except Exception as e:
        print(f"Error loading documents: {e}")
        raise
    finally:
        conn.close()


def get_db_path() -> str:
    """
    Get database path from environment or use default.

    Returns:
        Path to the database file
    """
    # Try to load .env file if python-dotenv is available
    if load_dotenv is not None:
        # Look for .env file in the parent directory (www folder)
        script_dir = Path(__file__).parent
        env_path = script_dir.parent / '.env'
        if env_path.exists():
            load_dotenv(env_path)

    # Get database path from environment or use default
    db_path = os.getenv('AI_SEARCH_DB_PATH', 'database/database.sqlite')

    # If relative path, make it relative to the www directory
    if not os.path.isabs(db_path):
        script_dir = Path(__file__).parent
        www_dir = script_dir.parent
        db_path = str(www_dir / db_path)

    return db_path


def interactive():
    """Interactive mode for testing with live database."""
    print("=== AI Search Engine - Interactive Mode ===\n")

    try:
        db_path = get_db_path()
        print(f"Loading documents from: {db_path}")

        documents = load_documents_from_db(db_path)

        if not documents:
            print("No documents found in database.")
            return

        print(f"Loaded {len(documents)} document(s)\n")

        engine = AISearchEngine()
        result = engine.ingest(documents)

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

                results = engine.search(q, k=5)

                if not results:
                    print("No results found.\n")
                    continue

                print(f"\nTop {len(results)} results:")
                for r in results:
                    record = r['record']
                    print(f"\n[Rank {r['rank']} | Score: {r['score']:.3f}]")
                    print(f"  ID: {record.get('id', 'N/A')}")
                    print(f"  Title: {record['title']}")
                    print(f"  Body: {record['body'][:100]}{'...' if len(record['body']) > 100 else ''}")
                    if record.get('tags'):
                        print(f"  Tags: {', '.join(str(t) for t in record['tags'])}")
                print()

            except KeyboardInterrupt:
                print("\nExiting...")
                break
            except Exception as e:
                print(f"Error: {e}\n")

    except FileNotFoundError as e:
        print(f"Error: {e}")
        print("\nMake sure:")
        print("1. The database file exists")
        print("2. AI_SEARCH_DB_PATH is correctly set in .env")
        print("3. You've run migrations: php artisan migrate")
    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    # Check if running in API mode (with stdin input) or interactive mode
    if sys.stdin.isatty():
        interactive()
    else:
        main()
