<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import type { FolderTreeNode, Document } from '../types';

const props = defineProps<{
    nodes: FolderTreeNode[];
    currentPath?: string;
}>();

const emit = defineEmits<{
    selectFolder: [path: string];
    selectDocument: [document: Document];
    deleteDocument: [id: number];
    deleteFolder: [path: string];
    moveDocument: [documentId: number, newPath: string];
}>();

const expandedFolders = ref<Set<string>>(new Set(['/']));
const draggingDocument = ref<number | null>(null);
const dragOverFolder = ref<string | null>(null);

const toggleFolder = (path: string) => {
    if (expandedFolders.value.has(path)) {
        expandedFolders.value.delete(path);
    } else {
        expandedFolders.value.add(path);
    }
};

const isFolderExpanded = (path: string) => {
    return expandedFolders.value.has(path);
};

const onDragStart = (event: DragEvent, node: FolderTreeNode) => {
    if (node.type === 'file' && node.id) {
        draggingDocument.value = node.id;
        event.dataTransfer!.effectAllowed = 'move';
        event.dataTransfer!.setData('text/plain', node.id.toString());
    }
};

const onDragOver = (event: DragEvent, node: FolderTreeNode) => {
    if (node.type === 'folder' && draggingDocument.value) {
        event.preventDefault();
        event.dataTransfer!.dropEffect = 'move';
        dragOverFolder.value = node.path;
    }
};

const onDragLeave = () => {
    dragOverFolder.value = null;
};

const onDrop = (event: DragEvent, node: FolderTreeNode) => {
    event.preventDefault();

    if (node.type === 'folder' && draggingDocument.value) {
        const documentId = draggingDocument.value;

        // Extract filename from dragged document
        const sourcePath = props.nodes.find((n: FolderTreeNode) => n.id === documentId)?.path || '';
        const fileNameOnly = sourcePath.split('/').pop() || 'file';
        const newPath = `${node.path}/${fileNameOnly}`;

        emit('moveDocument', documentId, newPath);
    }

    draggingDocument.value = null;
    dragOverFolder.value = null;
};

const onDragEnd = () => {
    draggingDocument.value = null;
    dragOverFolder.value = null;
};

const getFileName = (path: string): string => {
    const parts = path.split('/');
    return parts[parts.length - 1] || path;
};

const sortedNodes = computed(() => {
    return [...props.nodes].sort((a, b) => {
        // Folders first, then files
        if (a.type !== b.type) {
            return a.type === 'folder' ? -1 : 1;
        }
        // Then alphabetically
        return a.name.localeCompare(b.name);
    });
});

const handleFileDoubleClick = (node: FolderTreeNode) => {
    if (node.id) {
        router.visit(`/document/${node.id}`);
    }
};

const handleDeleteFolder = (path: string) => {
    emit('deleteFolder', path);
};
</script>

<template>
    <div class="folder-tree">
        <div v-for="node in sortedNodes" :key="node.path" class="tree-node"
            :class="{ 'is-dragging-over': dragOverFolder === node.path }">
            <!-- Folder -->
            <div v-if="node.type === 'folder'" class="folder-item" @click="toggleFolder(node.path)"
                @dragover="onDragOver($event, node)" @dragleave="onDragLeave" @drop="onDrop($event, node)">
                <span class="folder-icon">
                    {{ isFolderExpanded(node.path) ? '📂' : '📁' }}
                </span>
                <span class="folder-name">{{ node.name }}</span>
                <button class="delete-btn folder-delete-btn" @click.stop="handleDeleteFolder(node.path)"
                    title="Delete folder and all contents">
                    🗑️
                </button>
                <span class="expand-icon">
                    {{ isFolderExpanded(node.path) ? '▼' : '▶' }}
                </span>
            </div>

            <!-- Folder children (recursive) -->
            <div v-if="node.type === 'folder' && isFolderExpanded(node.path) && node.children" class="folder-children">
                <FolderTree :nodes="node.children" :current-path="currentPath"
                    @select-folder="(path: string) => emit('selectFolder', path)"
                    @select-document="(doc: Document) => emit('selectDocument', doc)"
                    @delete-document="(id: number) => emit('deleteDocument', id)"
                    @delete-folder="(path: string) => emit('deleteFolder', path)"
                    @move-document="(docId: number, newPath: string) => emit('moveDocument', docId, newPath)" />
            </div>

            <!-- File -->
            <div v-else-if="node.type === 'file'" class="file-item" :class="{
                'is-selected': currentPath === node.path,
                'is-imported': node.document?.metadata?.is_imported
            }" :draggable="!node.document?.metadata?.is_imported" @dragstart="onDragStart($event, node)"
                @dragend="onDragEnd" @click="node.document && emit('selectDocument', node.document)"
                @dblclick="handleFileDoubleClick(node)">
                <span class="file-icon">{{ node.document?.metadata?.is_imported ? '🔗' : '📄' }}</span>
                <span class="file-name">{{ getFileName(node.path) }}</span>
                <button v-if="node.id && node.id > 0" class="delete-btn" @click.stop="emit('deleteDocument', node.id)"
                    title="Delete document">
                    🗑️
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.folder-tree {
    font-size: 0.9rem;
    user-select: none;
}

.tree-node {
    margin-left: 0;
}

.folder-item,
.file-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.2s ease;
    gap: 0.5rem;
}

.folder-item:hover,
.file-item:hover {
    background-color: #f3f4f6;
}

.folder-item {
    font-weight: 500;
    color: #374151;
}

.file-item {
    color: #6b7280;
    position: relative;
}

.file-item.is-selected {
    background-color: #e0e7ff;
    color: #4c51bf;
}

.file-item.is-imported {
    opacity: 0.8;
    font-style: italic;
}

.file-item.is-imported .file-name {
    color: #6366f1;
}

.folder-icon,
.file-icon {
    font-size: 1.1rem;
    flex-shrink: 0;
}

.folder-name,
.file-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.expand-icon {
    font-size: 0.7rem;
    color: #9ca3af;
    flex-shrink: 0;
}

.folder-children {
    margin-left: 1.25rem;
    border-left: 1px solid #e5e7eb;
    padding-left: 0.5rem;
}

.delete-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    font-size: 0.9rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.file-item:hover .delete-btn,
.folder-item:hover .delete-btn {
    opacity: 1;
}

.delete-btn:hover {
    transform: scale(1.1);
}

.folder-delete-btn {
    color: #ef4444;
}

.is-dragging-over {
    background-color: #dbeafe !important;
    border: 2px dashed #3b82f6;
}

.file-item[draggable="true"] {
    cursor: move;
}
</style>
