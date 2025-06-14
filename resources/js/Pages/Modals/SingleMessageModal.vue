<template>
  <div class="modal-overlay active">
    <div class="modal-container">
      <div class="modal-header">
        <h3>Sending to {{ recipient?.name }}</h3>
        <button @click="$emit('close')" class="text-2xl">&times;</button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="sendMessage">
          <div class="mb-4">
            <label class="block mb-2">Select Message</label>
            <select v-model="selectedMessage" class="w-full p-2 border rounded">
              <option v-for="(msg, idx) in predefinedMessages[recipient.type]" :key="idx" :value="msg">
                {{ msg.name }}
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-2">Custom Message</label>
            <textarea v-model="customMessage" rows="4" class="w-full p-2 border rounded"></textarea>
          </div>
          <div class="mb-4">
            <label class="block mb-2">Send Via</label>
            <select v-model="method" class="w-full p-2 border rounded">
              <option value="email">Email</option>
              <option value="sms">SMS</option>
              <option value="both">Both</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block mb-2">Schedule Send</label>
            <input type="datetime-local" v-model="scheduledAt" class="w-full p-2 border rounded" />
          </div>
          <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Send</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  recipient: Object,
  predefinedMessages: Object
});

const emit = defineEmits(['close', 'send']);

const selectedMessage = ref(props.predefinedMessages[props.recipient.type][0]);
const customMessage = ref('');
const method = ref('email');
const scheduledAt = ref('');

const sendMessage = () => {
  const messageToSend = customMessage.value || selectedMessage.value.content;

  emit('send', {
    recipient: props.recipient,
    message: messageToSend,
    method: method.value,
    scheduled_at: scheduledAt.value
  });
};
</script>