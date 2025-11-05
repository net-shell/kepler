<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import PublicLayout from '../../layouts/Public.vue';

interface Props {
    status?: string;
}

defineProps<Props>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <PublicLayout>
        <div class="auth-page">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Reset Password</h1>
                        <p>Enter your email to receive a password reset link</p>
                    </div>

                    <div v-if="status" class="alert alert-success">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="auth-form">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" v-model="form.email" type="email" class="form-input"
                                :class="{ 'form-input-error': form.errors.email }" required autofocus
                                autocomplete="username" />
                            <div v-if="form.errors.email" class="form-error">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full" :disabled="form.processing">
                            <span v-if="form.processing">Sending...</span>
                            <span v-else>Send Reset Link</span>
                        </button>

                        <div class="auth-links">
                            <a href="/login" class="auth-link">
                                <strong>← Back to login</strong>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.auth-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    padding: 3rem;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #64748b;
    font-size: 1rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95rem;
}

.form-input {
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

.form-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input-error {
    border-color: #ef4444;
}

.form-input-error:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-error {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-full {
    width: 100%;
}

.auth-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    text-align: center;
    margin-top: 0.5rem;
}

.auth-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.3s ease;
}

.auth-link:hover {
    color: #764ba2;
    text-decoration: underline;
}

@media (max-width: 640px) {
    .auth-card {
        padding: 2rem;
    }

    .auth-header h1 {
        font-size: 1.75rem;
    }
}
</style>
