<template>
  <Head  />
  
  <AdminLayout title="Login Attempts">
    <div class="login-attempts-container">
      <div class="header">
        <p class="subtitle">Recent authentication attempts to the admin panel</p>
      </div>

      <div class="card attempts-table">
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Email</th>
                <th>IP Address</th>
                <th>Attempted At</th>
                <th>Status</th>
                <th>User Agent</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="attempt in attempts.data" :key="attempt.email + attempt.attempted_at">
                <td>{{ attempt.email }}</td>
                <td>{{ attempt.ip_address }}</td>
                <td>{{ attempt.attempted_at }}</td>
                <td>
                  <span class="status-badge" :class="attempt.successful ? 'success' : 'failed'">
                    {{ attempt.successful ? 'Success' : 'Failed' }}
                  </span>
                </td>
                <td class="user-agent">{{ attempt.user_agent }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pagination">
          <Link 
            v-for="link in attempts.links" 
            :key="link.label"
            :href="link.url || '#'"
            class="pagination-link"
            :class="{ 
              'active': link.active,
              'disabled': !link.url
            }"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  attempts: Object
});
</script>

<style scoped>
.login-attempts-container {
  max-width: 1200px;
  margin: 0 auto;
  color: var(--text-primary);
}

.header {
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--primary);
}

.subtitle {
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.attempts-table {
  padding: 1.5rem;
}

.table-responsive {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--card-border);
}

th {
  font-weight: 600;
  color: var(--primary);
  position: sticky;
  top: 0;
}

tr:hover {
  background-color: rgba(255, 255, 255, 0.03);
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.success {
  background-color: rgba(72, 187, 120, 0.1);
  color: #48bb78;
}

.status-badge.failed {
  background-color: rgba(245, 101, 101, 0.1);
  color: #f56565;
}

.user-agent {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
  gap: 0.5rem;
}

.pagination-link {
  padding: 0.5rem 1rem;
  border-radius: 0.25rem;
  background-color: var(--bg-darker);
  color: var(--text-primary);
  text-decoration: none;
  transition: all 0.2s;
}

.pagination-link:hover:not(.disabled) {
  background-color: var(--primary-light);
  color: var(--primary);
}

.pagination-link.active {
  background-color: var(--primary);
  color: white;
}

.pagination-link.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .login-attempts-container {
    padding: 1rem;
  }
  
  th, td {
    padding: 0.75rem 0.5rem;
    font-size: 0.875rem;
  }
  
  .user-agent {
    max-width: 150px;
  }
}
</style>