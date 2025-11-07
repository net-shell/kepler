<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import type { User } from '../types';

const page = usePage<{
    auth: {
        user: User | null;
    };
}>();

const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="authenticated-layout">
        <header class="layout-header">
            <div class="header-container">
                <div class="header-top">
                    <h1 class="logo">AI Search Dashboard</h1>
                    <div class="header-actions">
                        <span v-if="user" class="user-name">{{ user.name }}</span>
                        <Link href="/dashboard" class="nav-link">🏠 Dashboard</Link>
                        <Link href="/documents" class="nav-link">📋 Documents</Link>
                        <Link href="/data-sources" class="nav-link">🔌 Data Sources</Link>
                        <Link href="/" class="nav-link">← Back to Landing</Link>
                        <Link href="/logout" method="post" as="button" class="logout-btn">
                        Logout
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="layout-main">
            <slot />
        </main>
    </div>
</template>

<style scoped>
.authenticated-layout {
    min-height: 100vh;
    background: #f8fafc;
}

.layout-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 2rem;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 1.5rem;
    color: #2c3e50;
    margin: 0;
    font-weight: 700;
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

.nav-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 6px;
}

.nav-link:hover {
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

.layout-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}
</style>
