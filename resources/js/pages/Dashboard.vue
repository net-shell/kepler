<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import type { StatsResponse, User } from '../types';
import SearchComponent from '../components/SearchComponent.vue';
import DataFeedComponent from '../components/DataFeedComponent.vue';
import BulkUploadComponent from '../components/BulkUploadComponent.vue';
import DocumentList from '../components/DocumentList.vue';

const activeTab = ref<'search' | 'feed' | 'bulk' | 'list'>('search');
const stats = ref<StatsResponse | null>(null);
const loading = ref(false);

const page = usePage<{
    auth: {
        user: User | null;
    };
}>();

const user = computed(() => page.props.auth?.user);

const loadStats = async () => {
    try {
        loading.value = true;
        const response = await fetch('/api/search/stats');
        stats.value = await response.json();
    } catch (error) {
        console.error('Failed to load stats:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadStats();
});

const handleDataAdded = () => {
    loadStats();
};
</script>

<template>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="header-top">
                <h1>AI Search Dashboard</h1>
                <div class="header-actions">
                    <span v-if="user" class="user-name">{{ user.name }}</span>
                    <Link href="/data-sources" class="nav-link">🔌 Data Sources</Link>
                    <Link href="/" class="back-link">← Back to Landing</Link>
                    <Link href="/logout" method="post" as="button" class="logout-btn">
                    Logout
                    </Link>
                </div>
            </div>
            <div class="stats" v-if="stats">
                <div class="stat-card">
                    <span class="stat-label">Total Documents</span>
                    <span class="stat-value">{{ stats.total_documents }}</span>
                </div>
            </div>
        </header>

        <nav class="tabs">
            <button :class="{ active: activeTab === 'search' }" @click="activeTab = 'search'">
                🔍 Search
            </button>
            <button :class="{ active: activeTab === 'feed' }" @click="activeTab = 'feed'">
                ➕ Add Data
            </button>
            <button :class="{ active: activeTab === 'bulk' }" @click="activeTab = 'bulk'">
                📤 Bulk Upload
            </button>
            <button :class="{ active: activeTab === 'list' }" @click="activeTab = 'list'">
                📋 Documents
            </button>
        </nav>

        <main class="dashboard-content">
            <SearchComponent v-if="activeTab === 'search'" />
            <DataFeedComponent v-else-if="activeTab === 'feed'" @data-added="handleDataAdded" />
            <BulkUploadComponent v-else-if="activeTab === 'bulk'" @data-uploaded="handleDataAdded" />
            <DocumentList v-else-if="activeTab === 'list'" @document-updated="loadStats" />
        </main>
    </div>
</template>

<style scoped>
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.dashboard-header {
    margin-bottom: 2rem;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.user-name {
    color: #64748b;
    font-weight: 600;
    font-size: 1rem;
}

.back-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 6px;
}

.back-link:hover {
    background: #f8fafc;
    color: #764ba2;
}

.logout-btn {
    background: #ef4444;
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.logout-btn:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

.dashboard-header h1 {
    font-size: 2.5rem;
    color: #2c3e50;
    margin: 0;
}

.stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    min-width: 200px;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
}

.tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.tabs button {
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    color: #64748b;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.tabs button:hover {
    color: #475569;
    background: #f8fafc;
}

.tabs button.active {
    color: #667eea;
    border-bottom-color: #667eea;
    font-weight: 600;
}

.dashboard-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
