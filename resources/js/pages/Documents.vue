<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';
import DocumentList from '../components/DocumentList.vue';
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Documents" />

        <div class="documents-page">
            <DocumentList />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.documents-page {
    width: 100%;
}
</style>
