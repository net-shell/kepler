<script setup lang="ts">
import { ref } from 'vue';
import type { DocumentFormData, DataResponse } from '../types';

const emit = defineEmits<{
    dataAdded: []
}>();

const formData = ref<DocumentFormData>({
    title: '',
    path: '',
    body: '',
    tags: [],
    metadata: {},
});

const tagInput = ref('');
const metadataKey = ref('');
const metadataValue = ref('');
const loading = ref(false);
const success = ref('');
const error = ref('');
const currentFolder = ref('/');

const addTag = () => {
    if (tagInput.value.trim() && !formData.value.tags.includes(tagInput.value.trim())) {
        formData.value.tags.push(tagInput.value.trim());
        tagInput.value = '';
    }
};

const removeTag = (index: number) => {
    formData.value.tags.splice(index, 1);
};

const addMetadata = () => {
    if (metadataKey.value.trim() && metadataValue.value.trim()) {
        formData.value.metadata[metadataKey.value.trim()] = metadataValue.value.trim();
        metadataKey.value = '';
        metadataValue.value = '';
    }
};

const removeMetadata = (key: string) => {
    delete formData.value.metadata[key];
};

const submitForm = async () => {
    if (!formData.value.title.trim() || !formData.value.body.trim()) {
        error.value = 'Title and body are required';
        return;
    }

    try {
        loading.value = true;
        error.value = '';
        success.value = '';

        // Build path from folder and filename
        const fileName = formData.value.title.trim();
        const folder = currentFolder.value === '/' ? '' : currentFolder.value;
        const fullPath = `${folder}/${fileName}`;

        const dataToSend = {
            ...formData.value,
            path: formData.value.path || fullPath
        };

        const response = await fetch('/api/data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(dataToSend),
        });

        const data: DataResponse = await response.json();

        if (data.success) {
            success.value = data.message || 'Document created successfully!';
            resetForm();
            emit('dataAdded');
        } else {
            error.value = data.error || 'Failed to create document';
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'An error occurred';
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    formData.value = {
        title: '',
        path: '',
        body: '',
        tags: [],
        metadata: {},
    };
};
</script>

<template>
    <div class="data-feed-component">
        <h2>➕ Add New Document</h2>

        <div v-if="success" class="success-message">
            {{ success }}
        </div>

        <div v-if="error" class="error-message">
            {{ error }}
        </div>

        <form @submit.prevent="submitForm" class="feed-form">
            <div class="form-group">
                <label for="title">Title (Filename) *</label>
                <input id="title" v-model="formData.title" type="text" placeholder="Enter document title/filename"
                    :disabled="loading" required />
            </div>

            <div class="form-group">
                <label for="folder">Folder Path</label>
                <input id="folder" v-model="currentFolder" type="text" placeholder="/path/to/folder"
                    :disabled="loading" />
                <small class="help-text">
                    Full path will be: {{ currentFolder === '/' ? '' : currentFolder }}/{{ formData.title || 'filename'
                    }}
                </small>
            </div>

            <div class="form-group">
                <label for="full-path">Or enter full path directly</label>
                <input id="full-path" v-model="formData.path" type="text" placeholder="/custom/path/to/document"
                    :disabled="loading" />
                <small class="help-text">
                    Leave empty to use folder + title
                </small>
            </div>

            <div class="form-group">
                <label for="body">Body *</label>
                <textarea id="body" v-model="formData.body" placeholder="Enter document content" rows="6"
                    :disabled="loading" required></textarea>
            </div>

            <div class="form-group">
                <label>Tags</label>
                <div class="tag-input-group">
                    <input v-model="tagInput" type="text" placeholder="Add a tag" @keypress.enter.prevent="addTag"
                        :disabled="loading" />
                    <button type="button" @click="addTag" :disabled="loading">
                        Add Tag
                    </button>
                </div>
                <div v-if="formData.tags.length" class="tags-list">
                    <span v-for="(tag, index) in formData.tags" :key="index" class="tag">
                        {{ tag }}
                        <button type="button" @click="removeTag(index)" class="tag-remove" :disabled="loading">
                            ×
                        </button>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label>Metadata (Optional)</label>
                <div class="metadata-input-group">
                    <input v-model="metadataKey" type="text" placeholder="Key" :disabled="loading" />
                    <input v-model="metadataValue" type="text" placeholder="Value" @keypress.enter.prevent="addMetadata"
                        :disabled="loading" />
                    <button type="button" @click="addMetadata" :disabled="loading">
                        Add
                    </button>
                </div>
                <div v-if="Object.keys(formData.metadata).length" class="metadata-list">
                    <div v-for="(value, key) in formData.metadata" :key="key" class="metadata-item">
                        <strong>{{ key }}:</strong> {{ value }}
                        <button type="button" @click="removeMetadata(key)" class="metadata-remove" :disabled="loading">
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" :disabled="loading" class="submit-button">
                    {{ loading ? 'Creating...' : 'Create Document' }}
                </button>
                <button type="button" @click="resetForm" :disabled="loading" class="reset-button">
                    Reset
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.data-feed-component {
    width: 100%;
}

h2 {
    font-size: 1.75rem;
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.success-message {
    padding: 1rem;
    background: #d4edda;
    color: #155724;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.error-message {
    padding: 1rem;
    background: #fee;
    color: #c33;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.feed-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #475569;
    font-size: 0.875rem;
}

.form-group input,
.form-group textarea {
    padding: 0.75rem;
    font-size: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: border-color 0.3s ease;
    font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-group input:disabled,
.form-group textarea:disabled {
    background: #f8fafc;
    cursor: not-allowed;
}

.help-text {
    font-size: 0.75rem;
    color: #64748b;
    font-style: italic;
}

.tag-input-group,
.metadata-input-group {
    display: flex;
    gap: 0.5rem;
}

.tag-input-group input {
    flex: 1;
}

.metadata-input-group input {
    flex: 1;
}

.tag-input-group button,
.metadata-input-group button {
    padding: 0.75rem 1.5rem;
    background: #64748b;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.tag-input-group button:hover:not(:disabled),
.metadata-input-group button:hover:not(:disabled) {
    background: #475569;
}

.tags-list {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #e0e7ff;
    color: #4c51bf;
    padding: 0.5rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
}

.tag-remove {
    background: none;
    border: none;
    color: #4c51bf;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.metadata-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.metadata-item {
    background: #f8fafc;
    padding: 0.75rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.metadata-remove {
    margin-left: auto;
    background: none;
    border: none;
    color: #ef4444;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.form-actions {
    display: flex;
    gap: 1rem;
}

.submit-button {
    flex: 1;
    padding: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.submit-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.submit-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.reset-button {
    padding: 1rem 2rem;
    background: #f8fafc;
    color: #64748b;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
}

.reset-button:hover:not(:disabled) {
    background: #e2e8f0;
}

.reset-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
