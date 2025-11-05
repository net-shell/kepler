<script setup lang="ts">
import type { Document } from '../types';

defineProps<{
    document: Document;
    showDelete?: boolean;
}>();

const emit = defineEmits<{
    delete: [id: number];
    click: [document: Document];
}>();
</script>

<template>
    <div class="document-card" @click="emit('click', document)">
        <div class="document-header">
            <div class="title-section">
                <h3>{{ document.path || document.title }}</h3>
                <span v-if="document.path && document.path !== '/' + document.title" class="subtitle">
                    {{ document.title }}
                </span>
            </div>
            <button v-if="showDelete" @click.stop="emit('delete', document.id)" class="delete-button">
                🗑️ Delete
            </button>
        </div>
        <div class="document-footer">
            <div v-if="document.tags?.length" class="tags">
                <span v-for="tag in document.tags" :key="tag" class="tag">
                    {{ tag }}
                </span>
            </div>
            <span class="date">
                {{ new Date(document.created_at).toLocaleDateString() }}
            </span>
        </div>
    </div>
</template>

<style scoped>
.document-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}

.document-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.document-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.75rem;
}

.title-section {
    flex: 1;
}

.title-section h3 {
    font-size: 1.25rem;
    color: #2c3e50;
    margin: 0 0 0.25rem 0;
    word-break: break-all;
}

.subtitle {
    font-size: 0.875rem;
    color: #64748b;
}

.delete-button {
    background: #fee;
    color: #c33;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background 0.3s ease;
    flex-shrink: 0;
}

.delete-button:hover {
    background: #fcc;
}

.document-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
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

.date {
    color: #94a3b8;
    font-size: 0.875rem;
}
</style>
