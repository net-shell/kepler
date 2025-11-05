<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Document } from '../types';

const props = defineProps<{
    id: string;
}>();

const document = ref<Document | null>(null);
const loading = ref(false);
const error = ref('');

const loadDocument = async () => {
    try {
        loading.value = true;
        error.value = '';

        const response = await fetch(`/api/data/${props.id}`);
        const data = await response.json();

        if (data.success) {
            document.value = data.document;
        } else {
            error.value = data.error || 'Failed to load document';
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'An error occurred';
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    router.visit('/dashboard');
};

onMounted(() => {
    loadDocument();
});
</script>

<template>
    <div class="document-show">
        <div class="header">
            <button @click="goBack" class="back-button">
                ← Back
            </button>
            <h1>Document Details</h1>
        </div>

        <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>Loading document...</p>
        </div>

        <div v-else-if="error" class="error-message">
            {{ error }}
        </div>

        <div v-else-if="document" class="document-content">
            <div class="document-header">
                <h2>{{ document.path || document.title }}</h2>
            </div>

            <div class="document-meta">
                <div class="meta-row">
                    <strong>Title:</strong>
                    <span>{{ document.title }}</span>
                </div>
                <div class="meta-row">
                    <strong>Path:</strong>
                    <span>{{ document.path }}</span>
                </div>
                <div class="meta-row">
                    <strong>Created:</strong>
                    <span>{{ new Date(document.created_at).toLocaleDateString() }}</span>
                </div>
                <div class="meta-row">
                    <strong>Updated:</strong>
                    <span>{{ new Date(document.updated_at).toLocaleDateString() }}</span>
                </div>
                <div v-if="document.tags?.length" class="meta-row">
                    <strong>Tags:</strong>
                    <div class="tags">
                        <span v-for="tag in document.tags" :key="tag" class="tag">
                            {{ tag }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="document-body">
                <h3>Content</h3>
                <div class="body-content">
                    {{ document.body }}
                </div>
            </div>

            <div v-if="document.metadata" class="document-metadata">
                <h3>Metadata</h3>
                <pre>{{ JSON.stringify(document.metadata, null, 2) }}</pre>
            </div>
        </div>
    </div>
</template>

<style scoped>
.document-show {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.back-button {
    padding: 0.75rem 1.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.back-button:hover {
    background: #e2e8f0;
    transform: translateX(-2px);
}

h1 {
    font-size: 2rem;
    color: #2c3e50;
    margin: 0;
}

.loading {
    text-align: center;
    padding: 4rem;
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

.error-message {
    padding: 1.5rem;
    background: #fee;
    color: #c33;
    border-radius: 8px;
    margin-top: 2rem;
}

.document-content {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.document-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
}

.document-header h2 {
    margin: 0;
    font-size: 1.75rem;
    word-break: break-all;
}

.document-meta {
    padding: 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.meta-row {
    display: flex;
    gap: 1rem;
    align-items: start;
}

.meta-row strong {
    min-width: 100px;
    color: #64748b;
    font-size: 0.875rem;
}

.meta-row span {
    color: #2c3e50;
    word-break: break-all;
    flex: 1;
}

.tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tag {
    background: #e0e7ff;
    color: #4c51bf;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.document-body {
    padding: 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.document-body h3 {
    color: #475569;
    margin-top: 0;
    margin-bottom: 1rem;
}

.body-content {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 8px;
    white-space: pre-wrap;
    word-wrap: break-word;
    line-height: 1.6;
    color: #2c3e50;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

.document-metadata {
    padding: 2rem;
}

.document-metadata h3 {
    color: #475569;
    margin-top: 0;
    margin-bottom: 1rem;
}

.document-metadata pre {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 0.875rem;
    color: #2c3e50;
}
</style>
