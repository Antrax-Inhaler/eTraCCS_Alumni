<template>
  <AdminLayout title="Non-System Alumni Messaging">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Alumni Not Using eTraCCS
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-blue-800">Total Alumni</h3>
                <p class="text-2xl font-bold text-blue-900">{{ stats.total }}</p>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-green-800">With Email</h3>
                <p class="text-2xl font-bold text-green-900">{{ stats.with_email }}</p>
              </div>
              <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-purple-800">With Phone</h3>
                <p class="text-2xl font-bold text-purple-900">{{ stats.with_phone }}</p>
              </div>
            </div>

            <div class="flex justify-between items-center mb-4">
              <div class="flex space-x-2">
                <button 
                  @click="openBulkMessageModal"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                >
                  Send Bulk Message
                </button>
              </div>
              <div class="w-64">
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search alumni..."
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                >
              </div>
            </div>

            <div class="overflow-x-auto" style="color: white !important;">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                      <input 
                        type="checkbox" 
                        v-model="selectAll"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      >
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                      Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                      Contact
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                      Degree
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                      Year
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                      Last Message
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class=" divide-y divide-gray-200">
                  <tr v-for="alumnus in alumni.data" :key="alumnus.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input 
                        type="checkbox" 
                        v-model="selectedAlumni"
                        :value="alumnus.id"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium">{{ alumnus.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm ">{{ alumnus.email }}</div>
                      <div v-if="alumnus.contact_number" class="text-sm ">{{ alumnus.contact_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm ">{{ alumnus.degree_earned }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm ">{{ alumnus.year_graduated }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="alumnus.last_message_sent" class="text-sm ">{{ alumnus.last_message_sent }}</div>
                      <div v-else class="text-sm text-gray-400">Never</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link :href="route('admin.alumni-messaging.show', alumnus.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                        View
                      </Link>
                      <button @click="openMessageModal(alumnus)" class="text-green-600 hover:text-green-900">
                        Message
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-4">
              <Pagination :links="alumni.links" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Individual Message Modal -->
    <div class="modal-overlay" :class="{ active: showMessageModal }">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Send Message to {{ currentAlumni?.name }}</h3>
          <button class="modal-close" @click="closeMessageModal">&times;</button>
        </div>
        <div class="modal-body">
          <div v-if="loading" class="mb-4">
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div class="bg-blue-600 h-2.5 rounded-full" :style="`width: ${progress}%`"></div>
            </div>
            <div class="text-sm text-gray-600 mt-1">
              Sending {{ Math.round(progress) }}% complete ({{ Math.round(totalToSend * progress / 100) }} of {{ totalToSend }})
            </div>
          </div>
          
          <form @submit.prevent="sendMessage">
            <div class="mb-4">
              <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
              <input
                v-model="messageForm.subject"
                type="text"
                id="subject"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
              >
            </div>

            <div class="mb-4">
              <label for="content" class="block text-sm font-medium text-gray-700">Message</label>
              <textarea
                v-model="messageForm.content"
                id="content"
                rows="5"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
              ></textarea>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Send Via</label>
              <div class="mt-2 space-y-2">
                <div class="flex items-center">
                  <input
                    v-model="messageForm.method"
                    id="email-method"
                    type="radio"
                    value="email"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  >
                  <label for="email-method" class="ml-2 block text-sm text-gray-700">Email ({{ currentAlumni?.email || 'No email' }})</label>
                </div>
                <div class="flex items-center">
                  <input
                    v-model="messageForm.method"
                    id="sms-method"
                    type="radio"
                    value="sms"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                    :disabled="!currentAlumni?.contact_number"
                  >
                  <label for="sms-method" class="ml-2 block text-sm text-gray-700" :class="{ 'text-gray-400': !currentAlumni?.contact_number }">
                    SMS ({{ currentAlumni?.contact_number || 'No phone number' }})
                  </label>
                </div>
                <div class="flex items-center">
                  <input
                    v-model="messageForm.method"
                    id="both-method"
                    type="radio"
                    value="both"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                    :disabled="!currentAlumni?.email || !currentAlumni?.contact_number"
                  >
                  <label for="both-method" class="ml-2 block text-sm text-gray-700" :class="{ 'text-gray-400': !currentAlumni?.email || !currentAlumni?.contact_number }">
                    Both Email & SMS
                  </label>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Schedule Send (leave blank for immediate)</label>
              <input
                v-model="messageForm.scheduled_at"
                type="datetime-local"
                id="scheduled_at"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                :min="new Date().toISOString().slice(0, 16)"
              >
            </div>

            <div class="modal-footer">
              <button
                type="button"
                @click="closeMessageModal"
                class="mr-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover: focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                :disabled="isSubmitting"
              >
                <span v-if="!isSubmitting">Send Message</span>
                <span v-else>
                  <i class="fas fa-spinner fa-spin"></i> Sending...
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bulk Message Modal -->
    <div class="modal-overlay" :class="{ active: showBulkMessageModal }">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Send Message to {{ selectedAlumni.length }} Alumni</h3>
          <button class="modal-close" @click="closeBulkMessageModal">&times;</button>
        </div>
        <div class="modal-body">
          <div v-if="error" class="mb-4 p-4 bg-red-50 text-red-700 rounded">
            {{ error }}
          </div>
          
          <div v-if="loading" class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span class="ml-2">Sending messages...</span>
          </div>
          
          <form @submit.prevent="sendBulkMessage" v-if="!loading">
            <div class="mb-4">
              <label for="bulk-subject" class="block text-sm font-medium ">Subject</label>
              <input
                v-model="bulkMessageForm.subject"
                type="text"
                id="bulk-subject"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
              >
            </div>

            <div class="mb-4">
              <label for="bulk-content" class="block text-sm font-medium ">Message</label>
              <textarea
                v-model="bulkMessageForm.content"
                id="bulk-content"
                rows="5"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
              ></textarea>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium ">Send Via</label>
              <div class="mt-2 space-y-2">
                <div class="flex items-center">
                  <input
                    v-model="bulkMessageForm.method"
                    id="bulk-email-method"
                    type="radio"
                    value="email"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  >
                  <label for="bulk-email-method" class="ml-2 block text-sm text-gray-700">Email</label>
                </div>
                <div class="flex items-center">
                  <input
                    v-model="bulkMessageForm.method"
                    id="bulk-sms-method"
                    type="radio"
                    value="sms"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  >
                  <label for="bulk-sms-method" class="ml-2 block text-sm text-gray-700">SMS</label>
                </div>
                <div class="flex items-center">
                  <input
                    v-model="bulkMessageForm.method"
                    id="bulk-both-method"
                    type="radio"
                    value="both"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  >
                  <label for="bulk-both-method" class="ml-2 block text-sm text-gray-700">Both Email & SMS</label>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label for="bulk-scheduled_at" class="block text-sm font-medium text-gray-700">Schedule Send (leave blank for immediate)</label>
              <input
                v-model="bulkMessageForm.scheduled_at"
                type="datetime-local"
                id="bulk-scheduled_at"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                :min="new Date().toISOString().slice(0, 16)"
              >
            </div>

            <div class="modal-footer">
              <button
                type="button"
                @click="closeBulkMessageModal"
                class="mr-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover: focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
              >
                Send to {{ selectedAlumni.length }} Alumni
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const loading = ref(false);
const error = ref(null);
const progress = ref(0);
const totalToSend = ref(0);
const isSubmitting = ref(false);

const props = defineProps({
  alumni: Object,
  stats: Object,
});

const search = ref('');
const selectedAlumni = ref([]);
const selectAll = ref(false);
const showMessageModal = ref(false);
const showBulkMessageModal = ref(false);
const currentAlumni = ref(null);

const messageForm = ref({
  subject: '',
  content: '',
  method: 'email',
  scheduled_at: '',
});

const bulkMessageForm = ref({
  subject: '',
  content: '',
  method: 'email',
  scheduled_at: '',
});

watch(selectAll, (value) => {
  if (value) {
    selectedAlumni.value = props.alumni.data.map(alumnus => alumnus.id);
  } else {
    selectedAlumni.value = [];
  }
});

watch(search, (value) => {
  router.get(route('admin.alumni-messaging.index'), { search: value }, {
    preserveState: true,
    replace: true,
  });
});

function openMessageModal(alumnus) {
  currentAlumni.value = alumnus;
  messageForm.value = {
    subject: 'Join the eTraCCS Alumni Portal',
    content: `Dear ${alumnus.name},\n\nWe invite you to join our eTraCCS Alumni Portal to connect with fellow alumni and stay updated with university news and events.\n\nBest regards,\nThe Alumni Team`,
    method: alumnus.contact_number ? 'both' : 'email',
    scheduled_at: '',
  };
  showMessageModal.value = true;
}

function closeMessageModal() {
  showMessageModal.value = false;
}

function openBulkMessageModal() {
  if (selectedAlumni.value.length === 0) {
    alert('Please select at least one alumnus');
    return;
  }
  bulkMessageForm.value = {
    subject: 'Join the eTraCCS Alumni Portal',
    content: 'Dear Alumni,\n\nWe invite you to join our eTraCCS Alumni Portal to connect with fellow alumni and stay updated with university news and events.\n\nBest regards,\nThe Alumni Team',
    method: 'email',
    scheduled_at: '',
  };
  showBulkMessageModal.value = true;
}

function closeBulkMessageModal() {
  showBulkMessageModal.value = false;
}

function sendMessage() {
  isSubmitting.value = true;
  router.post(route('admin.alumni-messaging.send', currentAlumni.value.id), messageForm.value, {
    onSuccess: () => {
      closeMessageModal();
      isSubmitting.value = false;
    },
    onError: (errors) => {
      isSubmitting.value = false;
      error.value = Object.values(errors).join('\n');
    }
  });
}

async function sendBulkMessage() {
  loading.value = true;
  error.value = null;
  progress.value = 0;
  totalToSend.value = selectedAlumni.value.length;
  
  try {
    const chunkSize = 10;
    const chunks = [];
    
    for (let i = 0; i < selectedAlumni.value.length; i += chunkSize) {
      chunks.push(selectedAlumni.value.slice(i, i + chunkSize));
    }
    
    for (const [index, chunk] of chunks.entries()) {
      const response = await axios.post(route('admin.alumni-messaging.send-bulk'), {
        ...bulkMessageForm.value,
        alumni_ids: chunk,
      });
      
      progress.value = ((index + 1) / chunks.length) * 100;
      
      if (!response.data.success) {
        error.value = response.data.message || `Error sending batch ${index + 1}`;
        if (response.data.errors) {
          error.value += '\n' + response.data.errors.join('\n');
        }
      }
    }
    
    closeBulkMessageModal();
    selectedAlumni.value = [];
    selectAll.value = false;
    router.reload();
    
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'An unexpected error occurred';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

.modal-container {
  background: white;
  border-radius: 0.5rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  transform: translateY(-20px);
  transition: transform 0.3s ease;
}

.modal-overlay.active .modal-container {
  transform: translateY(0);
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6b7280;
  line-height: 1;
}

.modal-close:hover {
  color: #111827;
}

.modal-body {
  padding: 1rem;
}

.modal-footer {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

/* Loading spinner */
.loading-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  color: #3b82f6;
}

.fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>