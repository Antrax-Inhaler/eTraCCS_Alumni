<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Send Message to {{ recipient?.name }}</h3>
        <button @click="$emit('close')" class="text-xl font-bold">&times;</button>
      </div>

      <form @submit.prevent="$emit('send', messageForm)">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Subject</label>
          <input v-model="messageForm.subject" required class="w-full px-3 py-2 border rounded" />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Message</label>
          <textarea v-model="messageForm.content" required rows="4" class="w-full px-3 py-2 border rounded"></textarea>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Method</label>
          <select v-model="messageForm.method" class="w-full px-3 py-2 border rounded">
            <option value="email">Email</option>
            <option value="sms">SMS</option>
            <option value="both">Both</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Schedule Send</label>
          <input v-model="messageForm.scheduled_at" type="datetime-local" class="w-full px-3 py-2 border rounded" />
        </div>

        <div class="flex justify-end space-x-2">
          <button @click="$emit('close')" type="button" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Send</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  recipient: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'send']);

const messageForm = ref({
  subject: '',
  content: '',
  method: 'email',
  scheduled_at: null
});
</script>