<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />
<div class="container">

    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <AuthenticationCardLogo />
            </div>
            <h2 class="register-title">Welcome back</h2>
            <p class="register-subtitle">Log in to your account</p>
        </div>

        <div class="form-container">
            <div v-if="status" class="status-message">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="form-slide">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        class="form-control"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="email"
                    />
                    <InputError class="error-message" :message="form.errors.email" />
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-control"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="error-message" :message="form.errors.password" />
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            name="remember"
                        />
                        <span>Remember me</span>
                    </label>

                    <Link v-if="canResetPassword" :href="route('password.request')" class="forgot-password">
                        Forgot password?
                    </Link>
                </div>

                <div class="form-footer">
                    <div></div>
                    <PrimaryButton class="btn btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log in
                    </PrimaryButton>
                </div>
            </form>

            <div class="login-link">
                Don't have an account? <Link :href="route('register')" class="underline">Register</Link>
            </div>
        </div>
    </div>
</div>

</template>

<style scoped>
.container{
    display:flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}
/* Add these styles to match your register design */
.register-container {
    width: 100%;
    max-width: 500px;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.register-header {
    padding: 30px;
    text-align: center;
    border-bottom: 1px solid var(--card-border);
    position: relative;
}

.logo {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 10px;
}

.register-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.register-subtitle {
    color: var(--text-secondary);
    font-size: 14px;
}

.form-container {
    padding: 30px;
    position: relative;
}

.status-message {
    color: var(--success);
    margin-bottom: 20px;
    text-align: center;
    font-size: 14px;
}

.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px 0;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-secondary);
    font-size: 14px;
}

.remember-me input {
    accent-color: var(--primary);
}

.forgot-password {
    color: var(--primary);
    font-size: 14px;
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

.login-link {
    text-align: center;
    margin-top: 20px;
    color: var(--text-secondary);
}

.login-link a {
    color: var(--primary);
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

/* Reuse your existing styles from register */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-primary);
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 16px;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.2);
}

.form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--card-border);
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #e67e00;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 140, 0, 0.3);
}

.error-message {
    color: var(--error);
    font-size: 13px;
    margin-top: 5px;
}

@media (max-width: 576px) {
    .register-container {
        border-radius: 12px;
    }
    
    .register-header, .form-container {
        padding: 20px;
    }
        .register-container {
            border-radius: 12px;
        }
}
</style>