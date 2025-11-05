#!/usr/bin/env python3
"""
demo_data_loader.py - Load demo data into the AI Search application
--------------------------------------------------------------------
This script can be used to quickly populate the database with sample data
for testing and demonstration purposes.
"""

import requests
import json
import sys

# Configuration
BASE_URL = "http://localhost:8000/api"

# Sample documents to load
DEMO_DOCUMENTS = [
    {
        "title": "Getting Started with AI Search",
        "body": "Welcome to our AI-powered search system! This guide will help you understand how to use the semantic search features effectively. Start by adding documents through the dashboard.",
        "tags": ["guide", "tutorial", "getting-started"],
        "metadata": {"category": "documentation", "level": "beginner"}
    },
    {
        "title": "Advanced Search Techniques",
        "body": "Learn how to craft effective search queries. Use specific keywords, combine multiple terms, and leverage our TF-IDF algorithm for best results. The system understands context and semantic meaning.",
        "tags": ["guide", "advanced", "search", "techniques"],
        "metadata": {"category": "documentation", "level": "advanced"}
    },
    {
        "title": "API Integration Guide",
        "body": "Integrate our search API into your applications. We provide RESTful endpoints with JSON responses. Authentication, rate limiting, and error handling are all built-in.",
        "tags": ["API", "integration", "developer", "REST"],
        "metadata": {"category": "technical", "level": "intermediate"}
    },
    {
        "title": "Data Privacy and Security",
        "body": "Your data security is our top priority. All data is encrypted at rest and in transit. We never share your information with third parties. GDPR and CCPA compliant.",
        "tags": ["security", "privacy", "compliance", "legal"],
        "metadata": {"category": "legal", "importance": "critical"}
    },
    {
        "title": "Machine Learning Basics",
        "body": "Our system uses TF-IDF (Term Frequency-Inverse Document Frequency) and cosine similarity for semantic search. These proven techniques provide fast and accurate results without requiring deep learning.",
        "tags": ["machine-learning", "AI", "algorithms", "education"],
        "metadata": {"category": "technology", "level": "intermediate"}
    },
    {
        "title": "Troubleshooting Common Issues",
        "body": "Having trouble? Check our troubleshooting guide. Common issues include: slow searches (try reducing result limit), no results (check spelling), or connection errors (verify server is running).",
        "tags": ["troubleshooting", "help", "support", "FAQ"],
        "metadata": {"category": "support", "level": "beginner"}
    },
    {
        "title": "Performance Optimization Tips",
        "body": "Optimize your search performance by: 1) Using specific queries, 2) Limiting result count, 3) Regularly cleaning old documents, 4) Monitoring database size. Our system scales well to thousands of documents.",
        "tags": ["performance", "optimization", "tips", "best-practices"],
        "metadata": {"category": "technical", "level": "advanced"}
    },
    {
        "title": "Community Guidelines",
        "body": "Be respectful, helpful, and constructive. Share knowledge, ask questions, and help others. Our community thrives on collaboration and mutual support.",
        "tags": ["community", "guidelines", "policy", "social"],
        "metadata": {"category": "community", "importance": "medium"}
    },
    {
        "title": "Feature Roadmap 2024",
        "body": "Upcoming features: Multi-language support, image search, voice queries, enhanced analytics, mobile app, and enterprise features. Stay tuned for updates!",
        "tags": ["roadmap", "features", "future", "updates"],
        "metadata": {"category": "announcements", "year": "2024"}
    },
    {
        "title": "Success Stories",
        "body": "Hear from our users! Companies worldwide use our search system to improve customer service, organize knowledge bases, and enhance user experience. Join thousands of satisfied users.",
        "tags": ["testimonials", "success", "case-studies", "users"],
        "metadata": {"category": "marketing", "sentiment": "positive"}
    }
]


def load_documents(base_url=BASE_URL):
    """Load demo documents into the application."""
    print("🚀 AI Search Demo Data Loader")
    print("=" * 50)
    print()
    
    # Check if server is running
    try:
        response = requests.get(f"{base_url}/data", timeout=5)
        print(f"✅ Server is running at {base_url}")
        print()
    except requests.exceptions.RequestException as e:
        print(f"❌ Error: Cannot connect to {base_url}")
        print(f"   Make sure the Laravel server is running:")
        print(f"   php artisan serve")
        sys.exit(1)
    
    # Load documents
    success_count = 0
    error_count = 0
    
    for i, doc in enumerate(DEMO_DOCUMENTS, 1):
        try:
            response = requests.post(
                f"{base_url}/data",
                json=doc,
                headers={"Content-Type": "application/json"},
                timeout=10
            )
            
            if response.status_code in [200, 201]:
                success_count += 1
                print(f"✅ [{i}/{len(DEMO_DOCUMENTS)}] Created: {doc['title']}")
            else:
                error_count += 1
                print(f"❌ [{i}/{len(DEMO_DOCUMENTS)}] Failed: {doc['title']}")
                print(f"   Response: {response.text}")
                
        except requests.exceptions.RequestException as e:
            error_count += 1
            print(f"❌ [{i}/{len(DEMO_DOCUMENTS)}] Error: {doc['title']}")
            print(f"   {str(e)}")
    
    print()
    print("=" * 50)
    print(f"✅ Successfully loaded: {success_count} documents")
    if error_count > 0:
        print(f"❌ Failed: {error_count} documents")
    print()
    
    # Test search
    if success_count > 0:
        print("Testing search functionality...")
        try:
            search_response = requests.post(
                f"{base_url}/search",
                json={"query": "getting started", "limit": 3},
                headers={"Content-Type": "application/json"},
                timeout=10
            )
            
            if search_response.status_code == 200:
                results = search_response.json()
                if results.get("success"):
                    print(f"✅ Search is working! Found {len(results.get('results', []))} results")
                    for result in results.get('results', [])[:3]:
                        score = result.get('score', 0) * 100
                        title = result.get('record', {}).get('title', 'Unknown')
                        print(f"   • [{score:.1f}%] {title}")
                else:
                    print("⚠️  Search returned but with errors")
            else:
                print("⚠️  Search test failed")
                
        except Exception as e:
            print(f"⚠️  Could not test search: {e}")
    
    print()
    print("🎉 Demo data loading complete!")
    print()
    print("Next steps:")
    print("1. Open http://localhost:8000 in your browser")
    print("2. Try searching for: 'getting started', 'security', 'API'")
    print("3. Explore the different tabs in the dashboard")
    print()


if __name__ == "__main__":
    # Allow custom base URL from command line
    if len(sys.argv) > 1:
        custom_url = sys.argv[1]
        load_documents(custom_url)
    else:
        load_documents()
