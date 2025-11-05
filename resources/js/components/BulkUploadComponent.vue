<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits(['data-uploaded']);

const file = ref<File | null>(null);
const uploading = ref(false);
const uploadProgress = ref(0);
const uploadResult = ref<{ success: boolean; message: string; count?: number } | null>(null);
const dragActive = ref(false);

const acceptedFormats = [
    '.csv',
    '.txt',
    '.xlsx',
    '.xls',
    '.pdf',
    '.json',
    '.tsv'
];

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        file.value = target.files[0];
        uploadResult.value = null;
    }
};

const handleDrop = (event: DragEvent) => {
    dragActive.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        file.value = event.dataTransfer.files[0];
        uploadResult.value = null;
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    dragActive.value = true;
};

const handleDragLeave = () => {
    dragActive.value = false;
};

const uploadFile = async () => {
    if (!file.value) {
        uploadResult.value = {
            success: false,
            message: 'Please select a file first'
        };
        return;
    }

    uploading.value = true;
    uploadProgress.value = 0;
    uploadResult.value = null;

    try {
        const formData = new FormData();
        formData.append('file', file.value);

        const response = await fetch('/api/data/bulk-upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();

        if (response.ok) {
            uploadResult.value = {
                success: true,
                message: data.message || 'File uploaded successfully',
                count: data.count
            };
            file.value = null;
            emit('data-uploaded');
        } else {
            uploadResult.value = {
                success: false,
                message: data.error || 'Upload failed'
            };
        }
    } catch (error) {
        uploadResult.value = {
            success: false,
            message: error instanceof Error ? error.message : 'An error occurred during upload'
        };
    } finally {
        uploading.value = false;
        uploadProgress.value = 0;
    }
};

const clearFile = () => {
    file.value = null;
    uploadResult.value = null;
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <div class="bulk-upload">
        <div class="upload-header">
            <h2>📤 Bulk Upload Documents</h2>
            <p class="description">
                Upload files to automatically extract and index their content. Supports CSV, TXT, Excel, PDF, JSON, and
                TSV formats.
            </p>
        </div>

        <div class="upload-zone" :class="{ 'drag-active': dragActive, 'has-file': file }" @drop.prevent="handleDrop"
            @dragover.prevent="handleDragOver" @dragleave="handleDragLeave">
            <div v-if="!file" class="upload-placeholder">
                <div class="upload-icon">📁</div>
                <p class="upload-text">Drag and drop your file here</p>
                <p class="upload-subtext">or</p>
                <label class="file-input-label">
                    <input type="file" @change="handleFileChange" :accept="acceptedFormats.join(',')"
                        class="file-input" />
                    <span class="browse-button">Browse Files</span>
                </label>
                <p class="supported-formats">
                    Supported: {{ acceptedFormats.join(', ') }}
                </p>
            </div>

            <div v-else class="file-preview">
                <div class="file-info">
                    <div class="file-icon">📄</div>
                    <div class="file-details">
                        <p class="file-name">{{ file.name }}</p>
                        <p class="file-size">{{ formatFileSize(file.size) }}</p>
                    </div>
                    <button @click="clearFile" class="clear-button" :disabled="uploading">
                        ✕
                    </button>
                </div>

                <button @click="uploadFile" :disabled="uploading" class="upload-button">
                    <span v-if="!uploading">Upload & Process</span>
                    <span v-else>Processing...</span>
                </button>

                <div v-if="uploading" class="progress-bar">
                    <div class="progress-fill" :style="{ width: '50%' }"></div>
                </div>
            </div>
        </div>

        <div v-if="uploadResult" class="upload-result" :class="uploadResult.success ? 'success' : 'error'">
            <p class="result-message">
                {{ uploadResult.message }}
                <span v-if="uploadResult.count"> ({{ uploadResult.count }} documents created)</span>
            </p>
        </div>

        <div class="format-info">
            <h3>📋 File Format Guidelines</h3>
            <div class="format-grid">
                <div class="format-item">
                    <strong>CSV/TSV:</strong>
                    <p>Must include 'title' and 'body' columns. Optional: 'tags', 'metadata'</p>
                </div>
                <div class="format-item">
                    <strong>Excel (.xlsx/.xls):</strong>
                    <p>First sheet will be processed. Same column requirements as CSV</p>
                </div>
                <div class="format-item">
                    <strong>PDF:</strong>
                    <p>Text will be extracted and stored. File name used as title</p>
                </div>
                <div class="format-item">
                    <strong>Text (.txt):</strong>
                    <p>Content stored as body. File name used as title</p>
                </div>
                <div class="format-item">
                    <strong>JSON:</strong>
                    <p>Array of objects with 'title' and 'body' fields</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bulk-upload {
    max-width: 800px;
    margin: 0 auto;
}

.upload-header {
    margin-bottom: 2rem;
    text-align: center;
}

.upload-header h2 {
    font-size: 1.75rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.description {
    color: #64748b;
    font-size: 0.95rem;
}

.upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 3rem 2rem;
    background: #f8fafc;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.upload-zone.drag-active {
    border-color: #667eea;
    background: #eef2ff;
}

.upload-zone.has-file {
    background: white;
    border-style: solid;
}

.upload-placeholder {
    text-align: center;
}

.upload-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.upload-text {
    font-size: 1.125rem;
    color: #475569;
    margin-bottom: 0.5rem;
}

.upload-subtext {
    color: #94a3b8;
    margin-bottom: 1rem;
}

.file-input {
    display: none;
}

.file-input-label {
    cursor: pointer;
}

.browse-button {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.browse-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.supported-formats {
    margin-top: 1rem;
    font-size: 0.875rem;
    color: #94a3b8;
}

.file-preview {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.file-icon {
    font-size: 2.5rem;
}

.file-details {
    flex: 1;
}

.file-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.file-size {
    font-size: 0.875rem;
    color: #64748b;
}

.clear-button {
    padding: 0.5rem;
    background: #fee2e2;
    border: none;
    border-radius: 50%;
    color: #dc2626;
    cursor: pointer;
    font-size: 1.25rem;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.clear-button:hover:not(:disabled) {
    background: #fecaca;
}

.clear-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.upload-button {
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.upload-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.progress-bar {
    width: 100%;
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.6;
    }
}

.upload-result {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.upload-result.success {
    background: #dcfce7;
    border: 1px solid #86efac;
}

.upload-result.error {
    background: #fee2e2;
    border: 1px solid #fca5a5;
}

.result-message {
    color: #2c3e50;
    font-weight: 500;
}

.upload-result.success .result-message {
    color: #166534;
}

.upload-result.error .result-message {
    color: #991b1b;
}

.format-info {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 8px;
}

.format-info h3 {
    font-size: 1.125rem;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.format-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.format-item {
    padding: 1rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.format-item strong {
    display: block;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.format-item p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}
</style>
