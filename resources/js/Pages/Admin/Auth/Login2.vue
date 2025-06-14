<template>
  <div class="container">
    <div class="admin-card">
      <div class="admin-header">
        <div class="logo">
          <span>🔒 Admin Portal</span>
        </div>
        <h2 class="admin-title">Administrator Access</h2>
        <p class="admin-subtitle">Restricted access to authorized personnel only</p>
      </div>

      <div class="form-container">
        <!-- Status Messages -->
        <div v-if="status" class="status-message">
          {{ status }}
        </div>
        <div v-if="form.errors.email" class="error-message">
          {{ form.errors.email }}
        </div>

        <form @submit.prevent="submit" class="form-slide">
          <div class="form-group">
            <label class="form-label">Admin Email</label>
            <input
              type="email"
              class="form-control"
              v-model="form.email"
              placeholder="admin@example.com"
              required
              autofocus
              :class="{ 'input-error': form.errors.email }"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <input
              type="password"
              class="form-control"
              v-model="form.password"
              placeholder="••••••••"
              required
              :class="{ 'input-error': form.errors.password }"
            />
          </div>

          <div class="form-footer">
            <PrimaryButton 
              class="btn btn-admin" 
              :class="{ 'opacity-25': form.processing }" 
              :disabled="form.processing"
            >
              <span v-if="form.processing">Authenticating...</span>
              <span v-else>Secure Login</span>
            </PrimaryButton>
          </div>
        </form>

        <div class="security-notice">
          <span class="security-icon">⚠️</span>
          <span>All access attempts are logged (IP: {{ ipAddress }})</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const page = usePage()
const status = computed(() => page.props.status)
const ipAddress = ref('')

// Get client IP (note: this is the client-side IP which may differ from server-side)
try {
  fetch('https://api.ipify.org?format=json')
    .then(response => response.json())
    .then(data => ipAddress.value = data.ip)
} catch {
  ipAddress.value = 'unknown'
}

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post('/admin/login', {
    preserveScroll: true,
    onError: () => {
      // Additional error handling if needed
    },
    onSuccess: () => {
      // Reset form on successful login
      form.reset('password')
    }
  })
}
</script>

<style scoped>
.container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
}

.admin-card {
  width: 100%;
  max-width: 500px;
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 16px;
  overflow: hidden;
  backdrop-filter: blur(12px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  transition: all 0.3s;
}

.admin-card:hover {
  box-shadow: 0 15px 35px rgba(0,0,0,0.4);
  transform: translateY(-3px);
}

.admin-header {
  padding: 30px;
  text-align: center;
  border-bottom: 1px solid var(--card-border);
  background: rgba(26, 26, 26, 0.7);
}

.logo {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 10px;
  color: var(--primary);
}

.admin-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 5px;
  color: var(--text-primary);
}

.admin-subtitle {
  color: var(--text-secondary);
  font-size: 14px;
}

.form-container {
  padding: 30px;
  position: relative;
}

.status-message {
  padding: 12px;
  margin-bottom: 20px;
  background: rgba(76, 175, 80, 0.2);
  color: #4CAF50;
  border-radius: 8px;
  text-align: center;
  font-size: 14px;
}

.error-message {
  padding: 12px;
  margin-bottom: 20px;
  background: rgba(244, 67, 54, 0.2);
  color: #F44336;
  border-radius: 8px;
  text-align: center;
  font-size: 14px;
}

.input-error {
  border-color: #F44336 !important;
}

.security-notice {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 20px;
  padding: 12px;
  background: rgba(255, 140, 0, 0.1);
  border-radius: 8px;
  color: var(--primary);
  font-size: 13px;
  border: 1px solid rgba(255, 140, 0, 0.2);
}

.security-icon {
  font-size: 16px;
}

.btn-admin {
  background: var(--primary);
  width: 100%;
  justify-content: center;
}

.btn-admin:hover {
  background: #e67e00;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 140, 0, 0.3);
}

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
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--card-border);
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 16px;
  transition: all 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-light);
}

.form-footer {
  margin-top: 30px;
}

@media (max-width: 576px) {
  .admin-card {
    border-radius: 12px;
  }
  
  .admin-header, .form-container {
    padding: 20px;
  }
}
</style>