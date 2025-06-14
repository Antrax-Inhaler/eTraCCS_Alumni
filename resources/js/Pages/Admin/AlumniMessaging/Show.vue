<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const props = defineProps({
  alumni: Object,
  messages: Object,
});

const showMessageModal = ref(false);

const messageForm = ref({
  subject: 'Join the eTraCCS Alumni Portal',
  content: `Dear ${props.alumni.name},\n\nWe invite you to join our eTraCCS Alumni Portal to connect with fellow alumni and stay updated with university news and events.\n\nBest regards,\nThe Alumni Team`,
  method: props.alumni.contact_number ? 'both' : 'email',
  scheduled_at: '',
});

function openMessageModal() {
  showMessageModal.value = true;
}

function closeMessageModal() {
  showMessageModal.value = false;
}

function sendMessage() {
  router.post(route('admin.alumni-messaging.send', props.alumni.id), messageForm.value, {
    onSuccess: () => {
      closeMessageModal();
      messageForm.value = {
        subject: 'Join the eTraCCS Alumni Portal',
        content: `Dear ${props.alumni.name},\n\nWe invite you to join our eTraCCS Alumni Portal to connect with fellow alumni and stay updated with university news and events.\n\nBest regards,\nThe Alumni Team`,
        method: props.alumni.contact_number ? 'both' : 'email',
        scheduled_at: '',
      };
    },
  });
}
</script>

<template>
  <AdminLayout title="Alumni Messages">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Messages to {{ alumni.name }}
        </h2>
        <button 
          @click="openMessageModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
        >
          Send New Message
        </button>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="mb-8">
              <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
              <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500">Email</p>
                  <p class="mt-1 text-sm text-gray-900">{{ alumni.email || 'Not available' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Phone</p>
                  <p class="mt-1 text-sm text-gray-900">{{ alumni.contact_number || 'Not available' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Degree</p>
                  <p class="mt-1 text-sm text-gray-900">{{ alumni.degree_earned }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Year Graduated</p>
                  <p class="mt-1 text-sm text-gray-900">{{ alumni.year_graduated }}</p>
                </div>
              </div>
            </div>

            <h3 class="text-lg font-medium text-gray-900 mb-4">Message History</h3>
            
            <div v-if="messages.data.length > 0" class="space-y-4">
              <div v-for="message in messages.data" :key="message.id" class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="text-md font-medium text-gray-900">{{ message.subject }}</h4>
                    <p class="text-sm text-gray-500 mt-1">Sent via {{ message.method }} • {{ message.sent_at }}</p>
                    <p v-if="message.scheduled_at" class="text-sm text-gray-500">Scheduled for {{ message.scheduled_at }}</p>
                  </div>
                  <span 
                    class="px-2 py-1 text-xs rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': message.status === 'sent',
                      'bg-yellow-100 text-yellow-800': message.status === 'pending',
                      'bg-blue-100 text-blue-800': message.status === 'scheduled',
                      'bg-red-100 text-red-800': message.status === 'failed'
                    }"
                  >
                    {{ message.status }}
                  </span>
                </div>
                <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ message.content }}</div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No messages have been sent to this alumnus yet.
            </div>

            <div class="mt-4">
              <Pagination :links="messages.links" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Message Modal -->
    <Modal :show="showMessageModal" @close="closeMessageModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Send Message to {{ alumni.name }}</h2>
        
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
                  :disabled="!alumni.email"
                >
                <label for="email-method" class="ml-2 block text-sm text-gray-700" :class="{ 'text-gray-400': !alumni.email }">
                  Email ({{ alumni.email || 'No email' }})
                </label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="messageForm.method"
                  id="sms-method"
                  type="radio"
                  value="sms"
                  class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  :disabled="!alumni.contact_number"
                >
                <label for="sms-method" class="ml-2 block text-sm text-gray-700" :class="{ 'text-gray-400': !alumni.contact_number }">
                  SMS ({{ alumni.contact_number || 'No phone number' }})
                </label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="messageForm.method"
                  id="both-method"
                  type="radio"
                  value="both"
                  class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  :disabled="!alumni.email || !alumni.contact_number"
                >
                <label for="both-method" class="ml-2 block text-sm text-gray-700" :class="{ 'text-gray-400': !alumni.email || !alumni.contact_number }">
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

          <div class="flex justify-end">
            <button
              type="button"
              @click="closeMessageModal"
              class="mr-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
            >
              Send Message
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AdminLayout>
</template>