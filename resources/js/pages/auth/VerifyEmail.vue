<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '../../layouts/Public.vue';
import type { User } from '../../types';

interface Props {
    status?: string;
}

defineProps<Props>();

const page = usePage<{
    auth: {
        user: User | null;
    };
}>();

const user = computed(() => page.props.auth?.user);

const form = useForm({});

const submit = () => {
    form.post('/email/verification-notification');
};
</script>

<template>
    <PublicLayout>
        <div class="auth-page">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Verify Your Email</h1>
                        <p>Please verify your email address to continue</p>
                    </div>

                    <div v-if="status === 'verification-link-sent'" class="alert alert-success">
                        A new verification link has been sent to your email address.
                    </div>

                    <div class="verification-info">
                        <p>
                            Thanks for signing up! Before getting started, could you verify your email address
                            by clicking on the link we just emailed to you? If you didn't receive the email,
                            we will gladly send you another.
                        </p>
                        <p v-if="user" class="email-address">
                            {{ user.email }}
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="auth-form">
                        <button type="submit" class="btn btn-primary btn-full" :disabled="form.processing">
                            <span v-if="form.processing">Sending...</span>
                            <span v-else>Resend Verification Email</span>
                        </button>

                        <div class="auth-links">
                            <Link href="/logout" method="post" as="button" class="auth-link-btn">
                            <strong>Logout</strong>
                            </Link>
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
    max-width: 500px;
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

.verification-info {
    margin-bottom: 2rem;
}

.verification-info p {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.email-address {
    font-weight: 600;
    color: #667eea;
    text-align: center;
    font-size: 1.1rem;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
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
