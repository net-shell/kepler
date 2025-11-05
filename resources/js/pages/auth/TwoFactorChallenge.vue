<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PublicLayout from '../../layouts/Public.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = () => {
    recovery.value = !recovery.value;
    form.clearErrors();
};

const submit = () => {
    form.post('/two-factor-challenge');
};
</script>

<template>
    <PublicLayout>
        <div class="auth-page">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Two-Factor Authentication</h1>
                        <p v-if="!recovery">
                            Please confirm access to your account by entering the authentication code
                            provided by your authenticator application.
                        </p>
                        <p v-else>
                            Please confirm access to your account by entering one of your emergency
                            recovery codes.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="auth-form">
                        <div v-if="!recovery" class="form-group">
                            <label for="code" class="form-label">Code</label>
                            <input id="code" v-model="form.code" type="text" inputmode="numeric" class="form-input"
                                :class="{ 'form-input-error': form.errors.code }" autofocus
                                autocomplete="one-time-code" />
                            <div v-if="form.errors.code" class="form-error">
                                {{ form.errors.code }}
                            </div>
                        </div>

                        <div v-else class="form-group">
                            <label for="recovery_code" class="form-label">Recovery Code</label>
                            <input id="recovery_code" v-model="form.recovery_code" type="text" class="form-input"
                                :class="{ 'form-input-error': form.errors.recovery_code }"
                                autocomplete="one-time-code" />
                            <div v-if="form.errors.recovery_code" class="form-error">
                                {{ form.errors.recovery_code }}
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full" :disabled="form.processing">
                            <span v-if="form.processing">Verifying...</span>
                            <span v-else>Verify</span>
                        </button>

                        <div class="auth-links">
                            <button type="button" @click="toggleRecovery" class="auth-link-btn">
                                <span v-if="!recovery">Use a recovery code</span>
                                <span v-else>Use an authentication code</span>
                            </button>
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
    font-size: 0.95rem;
    line-height: 1.6;
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

.auth-link-btn {
    color: #667eea;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
    transition: color 0.3s ease;
    padding: 0.5rem;
}

.auth-link-btn:hover {
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
