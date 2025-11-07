<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Document, FolderTreeResponse, FolderTreeNode } from '../types';
import FolderTree from './FolderTree.vue';

const emit = defineEmits<{
    documentUpdated: []
}>();

const documents = ref<Document[]>([]);
const folderTree = ref<FolderTreeNode[]>([]);
const loading = ref(false);
const selectedDocument = ref<Document | null>(null);
const currentView = ref<'tree' | 'list'>('tree');
const currentFolder = ref<string>('/');
const newFolderPath = ref('');
const showNewFolderInput = ref(false);

const loadFolderTree = async () => {
    try {
        loading.value = true;
        const response = await fetch('/api/data/folder-tree');
        const data: FolderTreeResponse = await response.json();

        if (data.success) {
            folderTree.value = data.tree;
        }
    } catch (error) {
        console.error('Failed to load folder tree:', error);
    } finally {
        loading.value = false;
    }
};

const loadAllDocuments = async () => {
    try {
        loading.value = true;
        const response = await fetch('/api/feed');
        const data = await response.json();
        documents.value = data.data || [];
    } catch (error) {
        console.error('Failed to load documents:', error);
    } finally {
        loading.value = false;
    }
};

const selectDocument = (doc: Document) => {
    selectedDocument.value = doc;
};

const selectFolder = (path: string) => {
    currentFolder.value = path;
};

const deleteDocument = async (id: number) => {
    if (!confirm('Are you sure you want to delete this document?')) {
        return;
    }

    try {
        const response = await fetch(`/api/data/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            if (selectedDocument.value?.id === id) {
                selectedDocument.value = null;
            }
            await loadFolderTree();
            await loadAllDocuments();
            emit('documentUpdated');
        } else {
            alert(data.error || 'Failed to delete document');
        }
    } catch (error) {
        alert('An error occurred while deleting the document');
    }
};

const moveDocument = async (documentId: number, newPath: string) => {
    try {
        const response = await fetch(`/api/data/${documentId}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ new_path: newPath }),
        });

        const data = await response.json();

        if (data.success) {
            await loadFolderTree();
            await loadAllDocuments();
            emit('documentUpdated');
        } else {
            alert(data.error || 'Failed to move document');
        }
    } catch (error) {
        alert('An error occurred while moving the document');
    }
};

const createFolder = () => {
    showNewFolderInput.value = true;
};

const saveNewFolder = async () => {
    if (!newFolderPath.value.trim()) {
        alert('Please enter a folder path');
        return;
    }

    // Create a placeholder document in the new folder
    try {
        const response = await fetch('/api/data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                title: '.folder',
                path: `${newFolderPath.value}/.folder`,
                body: 'Folder placeholder',
                tags: ['system'],
                metadata: { system: true, folder: true }
            }),
        });

        const data = await response.json();

        if (data.success) {
            newFolderPath.value = '';
            showNewFolderInput.value = false;
            await loadFolderTree();
            await loadAllDocuments();
            emit('documentUpdated');
        } else {
            alert(data.error || 'Failed to create folder');
        }
    } catch (error) {
        alert('An error occurred while creating the folder');
    }
};

const cancelNewFolder = () => {
    newFolderPath.value = '';
    showNewFolderInput.value = false;
};

const deleteFolder = async (path: string) => {
    // Count documents in folder
    const docsInFolder = documents.value.filter(doc =>
        doc.path && (doc.path.startsWith(path + '/') || doc.path === path)
    );
    const count = docsInFolder.length;

    const message = count > 0
        ? `Are you sure you want to delete the folder "${path}" and all ${count} document(s) inside it? This action cannot be undone.`
        : `Are you sure you want to delete the folder "${path}"?`;

    if (!confirm(message)) {
        return;
    }

    try {
        // Bulk delete all documents in the folder
        const ids = docsInFolder.map(doc => doc.id);

        if (ids.length > 0) {
            const response = await fetch('/api/data/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids }),
            });

            const data = await response.json();

            if (!data.success) {
                alert(data.error || 'Failed to delete folder');
                return;
            }
        }

        // Reload data
        await loadFolderTree();
        await loadAllDocuments();
        emit('documentUpdated');

        // Clear selection if deleted
        if (selectedDocument.value && docsInFolder.some(d => d.id === selectedDocument.value?.id)) {
            selectedDocument.value = null;
        }
    } catch (error) {
        alert('An error occurred while deleting the folder');
    }
};

const toggleView = () => {
    currentView.value = currentView.value === 'tree' ? 'list' : 'tree';
};

const viewDocument = (id: number) => {
    router.visit(`/document/${id}`);
};

onMounted(() => {
    loadFolderTree();
    loadAllDocuments();
});
</script>

<template>
    <div class="document-list">
        <div class="header">
            <h2>📋 Documents</h2>
            <div class="header-actions">
                <button @click="toggleView" class="view-toggle-btn">
                    {{ currentView === 'tree' ? '📑 List View' : '🗂️ Tree View' }}
                </button>
                <button @click="createFolder" class="create-folder-btn">
                    📁 New Folder
                </button>
                <span class="total">Total: {{ documents.length }}</span>
            </div>
        </div>

        <!-- New Folder Input -->
        <div v-if="showNewFolderInput" class="new-folder-form">
            <input v-model="newFolderPath" type="text" placeholder="/path/to/folder" class="folder-input" />
            <button @click="saveNewFolder" class="save-btn">Save</button>
            <button @click="cancelNewFolder" class="cancel-btn">Cancel</button>
        </div>

        <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>Loading documents...</p>
        </div>

        <div v-else-if="documents.length === 0" class="no-documents">
            <p>No documents found. Add your first document!</p>
        </div>

        <!-- Tree View -->
        <div v-else-if="currentView === 'tree'" class="tree-view">
            <FolderTree :nodes="folderTree" :current-path="selectedDocument?.path" @select-folder="selectFolder"
                @select-document="selectDocument" @delete-document="deleteDocument" @delete-folder="deleteFolder"
                @move-document="moveDocument" />

            <!-- Selected Document Details -->
            <div v-if="selectedDocument" class="document-details">
                <div class="details-header">
                    <h3>{{ selectedDocument.path || selectedDocument.title }}</h3>
                    <button @click="selectedDocument = null" class="close-btn">✕</button>
                </div>
                <div class="document-info">
                    <div class="info-row">
                        <strong>Title:</strong>
                        <span>{{ selectedDocument.title }}</span>
                    </div>
                    <div class="info-row">
                        <strong>Path:</strong>
                        <span>{{ selectedDocument.path }}</span>
                    </div>
                    <div class="info-row">
                        <strong>Created:</strong>
                        <span>{{ new Date(selectedDocument.created_at).toLocaleDateString() }}</span>
                    </div>
                    <div v-if="selectedDocument.tags?.length" class="info-row">
                        <strong>Tags:</strong>
                        <div class="tags">
                            <span v-for="tag in selectedDocument.tags" :key="tag" class="tag">
                                {{ tag }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- List View -->
        <div v-else class="list-view">
            <div v-for="doc in documents" :key="doc.id" class="document-card" @dblclick="viewDocument(doc.id)">
                <div class="document-header">
                    <div class="title-section">
                        <h3>{{ doc.path || doc.title }}</h3>
                        <span v-if="doc.path && doc.path !== '/' + doc.title" class="subtitle">{{ doc.title }}</span>
                    </div>
                    <button @click.stop="deleteDocument(doc.id)" class="delete-button">
                        🗑️ Delete
                    </button>
                </div>
                <div class="document-footer">
                    <div v-if="doc.tags?.length" class="tags">
                        <span v-for="tag in doc.tags" :key="tag" class="tag">
                            {{ tag }}
                        </span>
                    </div>
                    <span class="date">
                        {{ new Date(doc.created_at).toLocaleDateString() }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.document-list {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

h2 {
    font-size: 1.75rem;
    color: #2c3e50;
    margin: 0;
}

.total {
    color: #64748b;
    font-size: 0.875rem;
}

.view-toggle-btn,
.create-folder-btn {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: transform 0.2s ease;
}

.view-toggle-btn:hover,
.create-folder-btn:hover {
    transform: translateY(-2px);
}

.new-folder-form {
    display: flex;
    gap: 0.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.folder-input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 0.875rem;
}

.save-btn,
.cancel-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
}

.save-btn {
    background: #10b981;
    color: white;
}

.cancel-btn {
    background: #94a3b8;
    color: white;
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

.no-documents {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.tree-view {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 1.5rem;
    min-height: 400px;
}

.document-details {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
    height: fit-content;
}

.details-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.details-header h3 {
    font-size: 1.25rem;
    color: #2c3e50;
    margin: 0;
    word-break: break-all;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #94a3b8;
    padding: 0;
    line-height: 1;
}

.close-btn:hover {
    color: #475569;
}

.document-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-row {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-row strong {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 600;
}

.info-row span {
    color: #2c3e50;
    word-break: break-all;
}

.list-view {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

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
