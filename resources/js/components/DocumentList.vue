<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { Document, PaginatedResponse } from '../types';

const emit = defineEmits<{
  documentUpdated: []
}>();

const documents = ref<Document[]>([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const total = ref(0);

const loadDocuments = async (page: number = 1) => {
  try {
    loading.value = true;
    const response = await fetch(`/api/data?page=${page}&per_page=10`);
    const data: PaginatedResponse = await response.json();

    documents.value = data.data;
    currentPage.value = data.current_page;
    totalPages.value = data.last_page;
    total.value = data.total;
  } catch (error) {
    console.error('Failed to load documents:', error);
  } finally {
    loading.value = false;
  }
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
      await loadDocuments(currentPage.value);
      emit('documentUpdated');
    } else {
      alert(data.error || 'Failed to delete document');
    }
  } catch (error) {
    alert('An error occurred while deleting the document');
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    loadDocuments(currentPage.value + 1);
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    loadDocuments(currentPage.value - 1);
  }
};

onMounted(() => {
  loadDocuments();
});
</script>

<template>
  <div class="document-list">
    <div class="header">
      <h2>📋 All Documents</h2>
      <span class="total">Total: {{ total }}</span>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading documents...</p>
    </div>

    <div v-else-if="documents.length === 0" class="no-documents">
      <p>No documents found. Add your first document!</p>
    </div>

    <div v-else class="documents">
      <div
        v-for="doc in documents"
        :key="doc.id"
        class="document-card"
      >
        <div class="document-header">
          <h3>{{ doc.title }}</h3>
          <button @click="deleteDocument(doc.id)" class="delete-button">
            🗑️ Delete
          </button>
        </div>
        <p class="document-body">{{ doc.body }}</p>
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

    <div v-if="totalPages > 1" class="pagination">
      <button @click="prevPage" :disabled="currentPage === 1">
        ← Previous
      </button>
      <span class="page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button @click="nextPage" :disabled="currentPage === totalPages">
        Next →
      </button>
    </div>
  </div>
</template>

<style scoped>
.document-list {
  width: 100%;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
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
  to { transform: rotate(360deg); }
}

.no-documents {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.documents {
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

.document-header h3 {
  font-size: 1.25rem;
  color: #2c3e50;
  margin: 0;
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
}

.delete-button:hover {
  background: #fcc;
}

.document-body {
  color: #475569;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.document-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination button {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: transform 0.2s ease;
}

.pagination button:hover:not(:disabled) {
  transform: translateY(-2px);
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #64748b;
  font-size: 0.875rem;
}
</style>
