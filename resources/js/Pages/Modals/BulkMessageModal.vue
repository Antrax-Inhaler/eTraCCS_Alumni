<template>
  <div class="modal-overlay" :class="{ active: show }">
    <div class="modal-container max-w-2xl w-full mx-auto bg-white rounded-lg shadow-lg">
      <div class="modal-header p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Send Message to {{ recipients.length }} Recipient(s)</h3>
        <button @click="close" class="text-xl text-gray-500 hover:text-gray-800">&times;</button>
      </div>

      <!-- Error Alert -->
      <div v-if="error" class="p-4 bg-red-100 text-red-700 border-b border-red-200">
        {{ error }}
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="p-6 flex flex-col items-center justify-center space-y-4">
        <i class="fas fa-spinner fa-spin text-blue-500 text-3xl"></i>
        <span class="text-gray-600">Sending messages...</span>
        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
          <div
            class="bg-blue-600 h-2.5 rounded-full transition-all"
            :style="{ width: progress + '%' }"
          ></div>
        </div>
        <span>{{ progress }}% Complete ({{ sentCount }} of {{ recipients.length }})</span>
      </div>

      <!-- Form -->
      <form @submit.prevent="send" v-if="!loading" class="p-4">
        <!-- Predefined Messages -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Select Template</label>
          <select
            v-model="selectedTemplate"
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            @change="updateMessageFromTemplate"
          >
            <option v-for="(msg, index) in predefinedMessages" :key="index" :value="msg">
              {{ msg.name }}
            </option>
          </select>
        </div>

        <!-- Subject -->
        <div class="mb-4">
          <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
          <input
            id="subject"
            v-model="messageForm.subject"
            type="text"
            required
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Message Content -->
        <div class="mb-4">
          <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Message Content</label>
          <textarea
            id="content"
            v-model="messageForm.content"
            rows="5"
            required
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <!-- Send Method -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Send Via</label>
          <div class="space-y-2">
            <label class="flex items-center">
              <input
                type="radio"
                v-model="messageForm.method"
                value="email"
                class="mr-2 text-blue-600 focus:ring-blue-500"
              />
              <span>Email</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="messageForm.method"
                value="sms"
                class="mr-2 text-blue-600 focus:ring-blue-500"
              />
              <span>SMS</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="messageForm.method"
                value="both"
                class="mr-2 text-blue-600 focus:ring-blue-500"
              />
              <span>Both</span>
            </label>
          </div>
        </div>

        <!-- Schedule -->
        <div class="mb-4">
          <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">Schedule Send (leave blank for immediate)</label>
          <input
            id="scheduled_at"
            v-model="messageForm.scheduled_at"
            type="datetime-local"
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            :min="now"
          />
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-2 pt-4">
          <button
            type="button"
            @click="close"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Send to {{ recipients.length }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  recipients: {
    type: Array,
    default: () => []
  },
  predefinedMessages: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'send']);

// Form data
const messageForm = ref({
  subject: '',
  content: '',
  method: 'email',
  scheduled_at: null
});

const selectedTemplate = ref(null);

const loading = ref(false);
const error = ref(null);
const progress = ref(0);
const sentCount = ref(0);

const now = new Date().toISOString().slice(0, 16);

// Auto-fill message when template changes
const updateMessageFromTemplate = () => {
  if (selectedTemplate.value) {
    messageForm.value.subject = selectedTemplate.value.name;
    messageForm.value.content = selectedTemplate.value.content;
  }
};

const close = () => {
  emit('close');
};

const send = async () => {
  try {
    loading.value = true;
    error.value = null;

    // Simulate sending with progress bar
    let total = recipients.length;
    progress.value = 0;
    sentCount.value = 0;

    for (let i = 0; i < total; i++) {
      await simulateSendMessage(recipients[i]);
      sentCount.value++;
      progress.value = Math.round((sentCount.value / total) * 100);
    }

    emit('send', {
      recipients,
      ...messageForm.value
    });

    setTimeout(() => {
      close();
    }, 500);
  } catch (err) {
    console.error('Error sending messages:', err);
    error.value = 'Failed to send messages. Please try again.';
  } finally {
    loading.value = false;
  }
};

// Simulated API call (replace this with real axios/post)
const simulateSendMessage = (recipient) => {
  return new Promise(resolve => {
    setTimeout(() => {
      resolve();
    }, 200);
  });
};
</script>

<style scoped>
.modal-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  align-items: center;
  justify-content: center;
}
.modal-overlay.active {
  display: flex;
}
.modal-container {
  position: relative;
  background: white;
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>