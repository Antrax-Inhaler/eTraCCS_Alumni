<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  unverifiedUsers: Array
});

const verifyingId = ref(null);
const verificationMessage = ref({
  show: false,
  type: 'success',
  text: ''
});

const verifyUser = (id) => {
  verifyingId.value = id;
  verificationMessage.value.show = false;

  router.post(`/admin/alumni/verify/${id}`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      verificationMessage.value = {
        show: true,
        type: 'success',
        text: 'User has been verified successfully!'
      };
      verifyingId.value = null;
    },
    onError: () => {
      verificationMessage.value = {
        show: true,
        type: 'error',
        text: 'Failed to verify user. Please try again.'
      };
      verifyingId.value = null;
    }
  });
};
</script>

<template>
    <AdminLayout>
      <div class="main-content">
        <h1 class="page-title">Unverified Alumni</h1>

        <!-- Verification Message -->
        <div v-if="verificationMessage.show" 
             :class="{
               'bg-green-900 bg-opacity-20 border-green-700 text-green-300': verificationMessage.type === 'success',
               'bg-red-900 bg-opacity-20 border-red-700 text-red-300': verificationMessage.type === 'error'
             }" 
             class="border px-4 py-3 rounded-lg relative mb-4 flex items-center justify-between" role="alert">
          <span>{{ verificationMessage.text }}</span>
          <button @click="verificationMessage.show = false" class="text-current">
            <svg class="fill-current h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
              <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
          </button>
        </div>

        <div class="data-section">
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Profile</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Education</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in unverifiedUsers" :key="user.id">
                  <td>
                    <img :src="user.profile_photo_url" 
                         class="user-avatar"
                         alt="Profile Photo">
                  </td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>
                    <div v-if="user.educational_backgrounds?.length" class="education-details">
                      <div v-for="edu in user.educational_backgrounds" :key="edu.id">
                        <p class="institution">{{ edu.school_name }}</p>
                        <p v-if="edu.campus" class="campus">{{ edu.campus }}</p>
                        <p class="degree">{{ edu.degree }}</p>
                        <p class="course">{{ edu.course }}</p>
                        <p class="year">Graduated: {{ edu.year_graduated }}</p>
                      </div>
                    </div>
                    <div v-else class="no-data">No education data</div>
                  </td>
                  <td>
                    <div v-if="user.alumni_location" class="location-details">
                      <p class="country">{{ user.alumni_location.country || 'Unknown' }}</p>
                      <p class="city">{{ user.alumni_location.city || 'Unknown city' }}</p>
                    </div>
                    <div v-else class="no-data">No location data</div>
                  </td>
                  <td>
                    <span :class="{
                      'status-verified': user.is_verified, 
                      'status-pending': !user.is_verified
                    }">
                      {{ user.is_verified ? 'Verified' : 'Pending' }}
                    </span>
                  </td>
                  <td>
                    <button
                      v-if="!user.is_verified"
                      :disabled="verifyingId === user.id"
                      @click="verifyUser(user.id)"
                      class="verify-btn"
                      :class="{ 'verifying': verifyingId === user.id }"
                    >
                      <span v-if="verifyingId === user.id" class="spinner"></span>
                      {{ verifyingId === user.id ? 'Verifying...' : 'Verify' }}
                    </button>
                    <span v-else class="verified-label">Verified</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <p v-if="unverifiedUsers.length === 0" class="no-results">No unverified users found.</p>
        </div>
      </div>
    </AdminLayout>
</template>

<style scoped>
.main-content {
  padding: 20px;
}

.page-title {
  font-size: 24px;
  margin-bottom: 20px;
  color: var(--text-primary);
}

.data-section {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  backdrop-filter: blur(12px);
  border-radius: 10px;
  padding: 20px;
}

.table-responsive {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  color: var(--text-secondary);
}

th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid var(--card-border);
}

th {
  font-weight: 600;
  color: var(--text-secondary);
  font-size: 13px;
}

tr:hover td {
  background: rgba(255, 255, 255, 0.05);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  display: block;
  margin: 0 auto;
}

.education-details div {
  margin-bottom: 10px;
}

.education-details p {
  margin: 2px 0;
  font-size: 13px;
}

.education-details .institution {
  font-weight: 500;
  color: var(--text-primary);
}

.location-details p {
  margin: 2px 0;
  font-size: 13px;
}

.location-details .country {
  font-weight: 500;
  color: var(--text-primary);
}

.no-data {
  color: var(--text-secondary);
  font-style: italic;
  font-size: 13px;
}

.status-verified {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  background: rgba(76, 175, 80, 0.2);
  color: var(--success);
}

.status-pending {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  background: rgba(255, 193, 7, 0.2);
  color: var(--warning);
}

.verify-btn {
  background: var(--primary);
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.verify-btn:hover:not(:disabled) {
  background: #e67e00;
}

.verify-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.verify-btn.verifying {
  background: rgba(255, 140, 0, 0.5);
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.verified-label {
  color: var(--success);
  font-size: 13px;
  font-weight: 500;
}

.no-results {
  color: var(--text-secondary);
  text-align: center;
  padding: 20px;
  font-style: italic;
}

@media (max-width: 768px) {
  th, td {
    padding: 8px 10px;
  }
  
  .education-details p,
  .location-details p {
    font-size: 12px;
  }
  
  .verify-btn {
    padding: 4px 8px;
    font-size: 12px;
  }
}
</style>