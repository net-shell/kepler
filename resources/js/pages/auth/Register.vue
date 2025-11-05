<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import PublicLayout from '../../layouts/Public.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <PublicLayout>
        <div class="auth-page">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Create Account</h1>
                        <p>Join Kepler AI Search today</p>
                    </div>

                    <form @submit.prevent="submit" class="auth-form">
                        <div class="form-group">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" v-model="form.name" type="text" class="form-input"
                                :class="{ 'form-input-error': form.errors.name }" required autofocus
                                autocomplete="name" />
                            <div v-if="form.errors.name" class="form-error">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" v-model="form.email" type="email" class="form-input"
                                :class="{ 'form-input-error': form.errors.email }" required autocomplete="username" />
                            <div v-if="form.errors.email" class="form-error">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" v-model="form.password" type="password" class="form-input"
                                :class="{ 'form-input-error': form.errors.password }" required
                                autocomplete="new-password" />
                            <div v-if="form.errors.password" class="form-error">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                                class="form-input" :class="{ 'form-input-error': form.errors.password_confirmation }"
                                required autocomplete="new-password" />
                            <div v-if="form.errors.password_confirmation" class="form-error">
                                {{ form.errors.password_confirmation }}
                            </div>
                        </div>

                        <div class="form-group-checkbox">
                            <label class="checkbox-label">
                                <input v-model="form.terms" type="checkbox" class="checkbox-input" required />
                                <span>I agree to the Terms of Service and Privacy Policy</span>
                            </label>
                            <div v-if="form.errors.terms" class="form-error">
                                {{ form.errors.terms }}
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full" :disabled="form.processing">
                            <span v-if="form.processing">Creating account...</span>
                            <span v-else>Create Account</span>
                        </button>

                        <div class="auth-links">
                            <a href="/login" class="auth-link">
                                Already have an account? <strong>Sign in</strong>
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

.form-group-checkbox {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.95rem;
    color: #64748b;
}

.checkbox-input {
    width: 1.125rem;
    height: 1.125rem;
    cursor: pointer;
    accent-color: #667eea;
    margin-top: 0.125rem;
    flex-shrink: 0;
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
