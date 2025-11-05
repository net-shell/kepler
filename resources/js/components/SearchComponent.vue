<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DocumentCard from './DocumentCard.vue';
import type { SearchResult, SearchResponse } from '../types';

const query = ref('');
const limit = ref(5);
const results = ref<SearchResult[]>([]);
const loading = ref(false);
const error = ref('');
const searchPerformed = ref(false);

const search = async () => {
    if (!query.value.trim()) {
        error.value = 'Please enter a search query';
        return;
    }

    try {
        loading.value = true;
        error.value = '';
        searchPerformed.value = true;

        const response = await fetch('/api/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                query: query.value,
                limit: limit.value,
            }),
        });

        const data: SearchResponse = await response.json();

        if (data.success) {
            results.value = data.results;
        } else {
            error.value = data.error || 'Search failed';
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'An error occurred';
    } finally {
        loading.value = false;
    }
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        search();
    }
};

const viewDocument = (id: number) => {
    router.visit(`/document/${id}`);
};
</script>

<template>
    <div class="search-component">
        <h2>🔍 Search Documents</h2>

        <div class="search-form">
            <div class="search-input-group">
                <input v-model="query" type="text" placeholder="Enter your search query..." class="search-input"
                    @keypress="handleKeyPress" :disabled="loading" />
                <button @click="search" :disabled="loading" class="search-button">
                    {{ loading ? 'Searching...' : 'Search' }}
                </button>
            </div>

            <div class="search-options">
                <label>
                    Results:
                    <select v-model.number="limit" :disabled="loading">
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                    </select>
                </label>
            </div>
        </div>

        <div v-if="error" class="error-message">
            {{ error }}
        </div>

        <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>Searching...</p>
        </div>

        <div v-else-if="searchPerformed && results.length === 0" class="no-results">
            <p>No results found for "{{ query }}"</p>
        </div>

        <div v-else-if="results.length > 0" class="results">
            <h3>Results ({{ results.length }})</h3>
            <div v-for="result in results" :key="result.record.id" class="result-wrapper">
                <DocumentCard :document="result.record" @click="viewDocument(result.record.id)" />
                <div class="result-meta">
                    <span class="score-badge">
                        Score: {{ (result.score * 100).toFixed(1) }}%
                    </span>
                    <span class="rank">Rank #{{ result.rank }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.search-component {
    width: 100%;
}

h2 {
    font-size: 1.75rem;
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.search-form {
    margin-bottom: 2rem;
}

.search-input-group {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.search-input {
    flex: 1;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: border-color 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
}

.search-input:disabled {
    background: #f8fafc;
    cursor: not-allowed;
}

.search-button {
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.search-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.search-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.search-options {
    display: flex;
    gap: 1rem;
}

.search-options label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.875rem;
}

.search-options select {
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
}

.error-message {
    padding: 1rem;
    background: #fee;
    color: #c33;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.loading {
    text-align: center;
    padding: 3rem;
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto 1rem;
    border: 4px solid #f3f4f6;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.no-results {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.results h3 {
    font-size: 1.25rem;
    color: #475569;
    margin-bottom: 1rem;
}

.result-wrapper {
    margin-bottom: 1rem;
}

.result-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0 0 8px 8px;
    margin-top: -8px;
}

.score-badge {
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
}

.rank {
    color: white;
    font-size: 0.875rem;
    opacity: 0.9;
}
</style>
