<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';

interface DataSource {
    id: number;
    name: string;
    type: 'database' | 'url' | 'api';
    description?: string;
    enabled: boolean;
    cache_ttl: number;
    last_cached_at?: string;
    cache_valid: boolean;
    cache_expires_at?: string;
    config: Record<string, any>;
}

const sources = ref<DataSource[]>([]);
const loading = ref(false);
const showForm = ref(false);
const editingSource = ref<DataSource | null>(null);
const testResult = ref<any>(null);
const showPreview = ref(false);
const previewData = ref<any>(null);

// Form data
const formData = ref({
    name: '',
    type: 'url' as 'database' | 'url' | 'api',
    description: '',
    cache_ttl: 3600,
    enabled: true,
    config: {} as Record<string, any>,
});

const loadSources = async () => {
    try {
        loading.value = true;
        const response = await fetch('/api/data-sources');
        const data = await response.json();
        if (data.success) {
            sources.value = data.sources;
        }
    } catch (error) {
        console.error('Failed to load data sources:', error);
    } finally {
        loading.value = false;
    }
};

const createNewSource = () => {
    editingSource.value = null;
    formData.value = {
        name: '',
        type: 'url',
        description: '',
        cache_ttl: 3600,
        enabled: true,
        config: {},
    };
    showForm.value = true;
    testResult.value = null;
};

const editSource = (source: DataSource) => {
    editingSource.value = source;
    formData.value = {
        name: source.name,
        type: source.type,
        description: source.description || '',
        cache_ttl: source.cache_ttl,
        enabled: source.enabled,
        config: { ...source.config },
    };
    showForm.value = true;
    testResult.value = null;
};

const saveSource = async () => {
    try {
        loading.value = true;
        const url = editingSource.value
            ? `/api/data-sources/${editingSource.value.id}`
            : '/api/data-sources';

        const method = editingSource.value ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData.value),
        });

        const data = await response.json();

        if (data.success) {
            showForm.value = false;
            await loadSources();
        } else {
            alert('Failed to save data source: ' + data.message);
        }
    } catch (error) {
        console.error('Failed to save data source:', error);
        alert('Failed to save data source');
    } finally {
        loading.value = false;
    }
};

const deleteSource = async (id: number) => {
    if (!confirm('Are you sure you want to delete this data source?')) {
        return;
    }

    try {
        loading.value = true;
        const response = await fetch(`/api/data-sources/${id}`, {
            method: 'DELETE',
        });

        const data = await response.json();

        if (data.success) {
            await loadSources();
        }
    } catch (error) {
        console.error('Failed to delete data source:', error);
    } finally {
        loading.value = false;
    }
};

const toggleSource = async (id: number) => {
    try {
        const response = await fetch(`/api/data-sources/${id}/toggle`, {
            method: 'POST',
        });

        const data = await response.json();

        if (data.success) {
            await loadSources();
        }
    } catch (error) {
        console.error('Failed to toggle data source:', error);
    }
};

const refreshSource = async (id: number) => {
    try {
        loading.value = true;
        const response = await fetch(`/api/data-sources/${id}/refresh`, {
            method: 'POST',
        });

        const data = await response.json();

        if (data.success) {
            alert(`Cache refreshed! ${data.count} items cached.`);
            await loadSources();
        } else {
            alert('Failed to refresh: ' + data.message);
        }
    } catch (error) {
        console.error('Failed to refresh data source:', error);
        alert('Failed to refresh data source');
    } finally {
        loading.value = false;
    }
};

const testConnection = async () => {
    try {
        loading.value = true;
        testResult.value = null;

        const response = await fetch('/api/data-sources/test', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: formData.value.type,
                config: formData.value.config,
            }),
        });

        testResult.value = await response.json();
    } catch (error) {
        console.error('Failed to test connection:', error);
        testResult.value = { success: false, message: 'Connection test failed' };
    } finally {
        loading.value = false;
    }
};

const viewPreview = async (id: number) => {
    try {
        loading.value = true;
        const response = await fetch(`/api/data-sources/${id}/preview`);
        const data = await response.json();

        if (data.success) {
            previewData.value = data;
            showPreview.value = true;
        } else {
            alert('Failed to load preview: ' + data.message);
        }
    } catch (error) {
        console.error('Failed to load preview:', error);
        alert('Failed to load preview');
    } finally {
        loading.value = false;
    }
};

const formatDate = (date?: string) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
};

const formatTTL = (seconds: number) => {
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h`;
    return `${Math.floor(seconds / 86400)}d`;
};

const getTypeIcon = (type: string) => {
    return { database: '🗄️', url: '🌐', api: '🔌' }[type] || '📦';
};

onMounted(() => {
    loadSources();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="data-sources-page">

            <Head title="Data Sources" />

            <header class="page-header">
                <h1>🔌 Data Sources</h1>
                <p class="subtitle">Manage external data sources for AI search</p>
                <button @click="createNewSource" class="btn btn-primary">
                    ➕ Add Data Source
                </button>
            </header>

            <div v-if="loading && sources.length === 0" class="loading">
                Loading data sources...
            </div>

            <div v-else-if="sources.length === 0" class="empty-state">
                <p>No data sources configured yet.</p>
                <button @click="createNewSource" class="btn btn-primary">
                    Add your first data source
                </button>
            </div>

            <div v-else class="sources-grid">
                <div v-for="source in sources" :key="source.id" class="source-card"
                    :class="{ disabled: !source.enabled }">
                    <div class="source-header">
                        <span class="source-icon">{{ getTypeIcon(source.type) }}</span>
                        <div class="source-info">
                            <h3>{{ source.name }}</h3>
                            <span class="source-type">{{ source.type }}</span>
                        </div>
                        <div class="source-status">
                            <span class="status-badge" :class="{ active: source.enabled, inactive: !source.enabled }">
                                {{ source.enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>

                    <p v-if="source.description" class="source-description">
                        {{ source.description }}
                    </p>

                    <div class="source-meta">
                        <div class="meta-item">
                            <span class="meta-label">Cache TTL:</span>
                            <span class="meta-value">{{ formatTTL(source.cache_ttl) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Last Cached:</span>
                            <span class="meta-value">{{ formatDate(source.last_cached_at) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Cache Status:</span>
                            <span class="meta-value"
                                :class="{ 'text-success': source.cache_valid, 'text-warning': !source.cache_valid }">
                                {{ source.cache_valid ? '✓ Valid' : '⚠ Expired' }}
                            </span>
                        </div>
                    </div>

                    <div class="source-actions">
                        <button @click="viewPreview(source.id)" class="btn btn-sm" title="Preview Data">
                            👁️ Preview
                        </button>
                        <button @click="refreshSource(source.id)" class="btn btn-sm" title="Refresh Cache">
                            🔄 Refresh
                        </button>
                        <button @click="toggleSource(source.id)" class="btn btn-sm" title="Toggle Enabled">
                            {{ source.enabled ? '⏸️ Disable' : '▶️ Enable' }}
                        </button>
                        <button @click="editSource(source)" class="btn btn-sm" title="Edit">
                            ✏️ Edit
                        </button>
                        <button @click="deleteSource(source.id)" class="btn btn-sm btn-danger" title="Delete">
                            🗑️ Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Modal -->
            <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
                <div class="modal-content large">
                    <div class="modal-header">
                        <h2>{{ editingSource ? 'Edit Data Source' : 'Add Data Source' }}</h2>
                        <button @click="showForm = false" class="close-btn">✕</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input v-model="formData.name" type="text" placeholder="My Data Source" />
                        </div>

                        <div class="form-group">
                            <label>Type</label>
                            <select v-model="formData.type">
                                <option value="url">🌐 URL/File</option>
                                <option value="api">🔌 API Endpoint</option>
                                <option value="database">🗄️ Database Query</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea v-model="formData.description" placeholder="Optional description"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Cache TTL (seconds)</label>
                            <input v-model.number="formData.cache_ttl" type="number" min="0" />
                            <small>{{ formatTTL(formData.cache_ttl) }}</small>
                        </div>

                        <!-- URL Configuration -->
                        <template v-if="formData.type === 'url'">
                            <div class="config-section">
                                <h3>URL Configuration</h3>
                                <div class="form-group">
                                    <label>URL</label>
                                    <input v-model="formData.config.url" type="url"
                                        placeholder="https://example.com/data.json" />
                                </div>
                                <div class="form-group">
                                    <label>Format</label>
                                    <select v-model="formData.config.format">
                                        <option value="json">JSON</option>
                                        <option value="xml">XML</option>
                                        <option value="csv">CSV</option>
                                        <option value="rss">RSS Feed</option>
                                        <option value="text">Plain Text</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <!-- API Configuration -->
                        <template v-else-if="formData.type === 'api'">
                            <div class="config-section">
                                <h3>API Configuration</h3>
                                <div class="form-group">
                                    <label>URL</label>
                                    <input v-model="formData.config.url" type="url"
                                        placeholder="https://api.example.com/data" />
                                </div>
                                <div class="form-group">
                                    <label>Method</label>
                                    <select v-model="formData.config.method">
                                        <option value="get">GET</option>
                                        <option value="post">POST</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Authentication Type</label>
                                    <select v-model="formData.config.auth_type">
                                        <option value="none">None</option>
                                        <option value="bearer">Bearer Token</option>
                                        <option value="api_key">API Key</option>
                                        <option value="basic">Basic Auth</option>
                                    </select>
                                </div>
                                <div v-if="formData.config.auth_type === 'bearer'" class="form-group">
                                    <label>Bearer Token</label>
                                    <input v-model="formData.config.token" type="password" />
                                </div>
                                <div v-if="formData.config.auth_type === 'api_key'" class="form-group">
                                    <label>API Key Header Name</label>
                                    <input v-model="formData.config.api_key_header" placeholder="X-API-Key" />
                                    <label>API Key</label>
                                    <input v-model="formData.config.api_key" type="password" />
                                </div>
                                <div v-if="formData.config.auth_type === 'basic'">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input v-model="formData.config.username" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input v-model="formData.config.password" type="password" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Response Format</label>
                                    <select v-model="formData.config.format">
                                        <option value="json">JSON</option>
                                        <option value="xml">XML</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Data Path (optional)</label>
                                    <input v-model="formData.config.data_path" placeholder="e.g., data.items" />
                                    <small>Path to the array of items in the response (e.g., "data.items")</small>
                                </div>
                            </div>
                        </template>

                        <!-- Database Configuration -->
                        <template v-else-if="formData.type === 'database'">
                            <div class="config-section">
                                <h3>Database Configuration</h3>
                                <div class="form-group">
                                    <label>SQL Query</label>
                                    <textarea v-model="formData.config.query" placeholder="SELECT * FROM table"
                                        rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Connection (optional)</label>
                                    <input v-model="formData.config.connection"
                                        placeholder="mysql, pgsql, sqlite (default)" />
                                </div>
                            </div>
                        </template>

                        <!-- Test Result -->
                        <div v-if="testResult" class="test-result"
                            :class="{ success: testResult.success, error: !testResult.success }">
                            <h4>{{ testResult.success ? '✓ Test Successful' : '✗ Test Failed' }}</h4>
                            <p>{{ testResult.message }}</p>
                            <div v-if="testResult.success">
                                <p><strong>Sample Count:</strong> {{ testResult.sample_count }}</p>
                                <details v-if="testResult.sample && testResult.sample.length > 0">
                                    <summary>View Sample Data</summary>
                                    <pre>{{ JSON.stringify(testResult.sample, null, 2) }}</pre>
                                </details>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="testConnection" class="btn btn-secondary" :disabled="loading">
                            🧪 Test Connection
                        </button>
                        <button @click="showForm = false" class="btn">Cancel</button>
                        <button @click="saveSource" class="btn btn-primary" :disabled="loading">
                            {{ editingSource ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Modal -->
            <div v-if="showPreview" class="modal-overlay" @click.self="showPreview = false">
                <div class="modal-content large">
                    <div class="modal-header">
                        <h2>Data Preview</h2>
                        <button @click="showPreview = false" class="close-btn">✕</button>
                    </div>

                    <div class="modal-body">
                        <p><strong>Total Items:</strong> {{ previewData?.total_count || 0 }}</p>
                        <p><strong>Cached At:</strong> {{ formatDate(previewData?.cached_at) }}</p>
                        <pre class="preview-data">{{ JSON.stringify(previewData?.preview, null, 2) }}</pre>
                    </div>

                    <div class="modal-footer">
                        <button @click="showPreview = false" class="btn">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.data-sources-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.subtitle {
    color: #666;
    margin-bottom: 1rem;
}

.sources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.source-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
    transition: box-shadow 0.2s;
}

.source-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.source-card.disabled {
    opacity: 0.6;
}

.source-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.source-icon {
    font-size: 2rem;
}

.source-info {
    flex: 1;
}

.source-info h3 {
    margin: 0;
    font-size: 1.1rem;
}

.source-type {
    font-size: 0.85rem;
    color: #666;
    text-transform: uppercase;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-badge.active {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.inactive {
    background: #ffebee;
    color: #c62828;
}

.source-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.source-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f5f5f5;
    border-radius: 4px;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
}

.meta-label {
    font-weight: 500;
    color: #666;
}

.text-success {
    color: #2e7d32;
}

.text-warning {
    color: #f57c00;
}

.source-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.btn:hover {
    background: #f5f5f5;
}

.btn-primary {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}

.btn-primary:hover {
    background: #1565c0;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.btn-danger {
    background: #d32f2f;
    color: white;
    border-color: #d32f2f;
}

.btn-danger:hover {
    background: #c62828;
}

.btn-secondary {
    background: #757575;
    color: white;
    border-color: #757575;
}

.btn-secondary:hover {
    background: #616161;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.loading {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content.large {
    max-width: 800px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1.5rem;
    border-top: 1px solid #e0e0e0;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.95rem;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.85rem;
}

.config-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e0e0e0;
}

.config-section h3 {
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.test-result {
    margin-top: 1.5rem;
    padding: 1rem;
    border-radius: 4px;
}

.test-result.success {
    background: #e8f5e9;
    border: 1px solid #4caf50;
}

.test-result.error {
    background: #ffebee;
    border: 1px solid #f44336;
}

.test-result h4 {
    margin: 0 0 0.5rem 0;
}

.test-result pre {
    background: white;
    padding: 0.5rem;
    border-radius: 4px;
    overflow-x: auto;
    font-size: 0.85rem;
}

.preview-data {
    background: #f5f5f5;
    padding: 1rem;
    border-radius: 4px;
    overflow-x: auto;
    max-height: 400px;
}

details {
    margin-top: 0.5rem;
}

summary {
    cursor: pointer;
    font-weight: 500;
    padding: 0.5rem;
}

summary:hover {
    background: #f5f5f5;
}
</style>
