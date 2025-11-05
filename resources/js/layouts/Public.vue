<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '../types';

interface Props {
    showHeader?: boolean;
    showFooter?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showHeader: true,
    showFooter: true,
});

const page = usePage<{
    auth: {
        user: User | null;
    };
}>();

const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="public-layout">
        <header v-if="props.showHeader" class="public-header">
            <div class="header-container">
                <Link href="/" class="logo">
                <span class="gradient-text">Kepler AI Search</span>
                </Link>
                <nav class="header-nav">
                    <template v-if="user">
                        <Link href="/dashboard" class="nav-link">Dashboard</Link>
                        <Link href="/logout" method="post" as="button" class="nav-link nav-link-logout">
                        Logout
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="nav-link">Login</Link>
                        <Link href="/register" class="nav-link nav-link-primary">Sign Up</Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="public-main">
            <slot />
        </main>

        <footer v-if="props.showFooter" class="public-footer">
            <div class="footer-container">
                <p>&copy; 2025 Kepler AI Search. Built with Laravel, Vue 3, and Python.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.public-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    color: #2c3e50;
    background: #f8fafc;
}

.public-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    text-decoration: none;
    font-size: 1.5rem;
    font-weight: 700;
}

.gradient-text {
    background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header-nav {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.nav-link {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
}

.nav-link-primary {
    background: white;
    color: #667eea;
}

.nav-link-primary:hover {
    background: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.nav-link-logout {
    background: rgba(239, 68, 68, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.nav-link-logout:hover {
    background: rgba(239, 68, 68, 0.2);
}

.public-main {
    flex: 1;
}

.public-footer {
    background: #2c3e50;
    color: white;
    padding: 2rem;
    text-align: center;
    margin-top: auto;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

.public-footer p {
    opacity: 0.8;
    margin: 0;
}

@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        gap: 1rem;
    }

    .header-nav {
        width: 100%;
        justify-content: center;
    }
}
</style>
