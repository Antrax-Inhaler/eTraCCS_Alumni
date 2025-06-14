<script setup>
import { useMessageStore } from '@/Stores/messageStore';
const messageStore = useMessageStore();

function close() {
    messageStore.closeMessage();
}
</script>

<template>
  <div v-if="messageStore.isOpen" class="fixed bottom-4 right-4 w-80 bg-white shadow-lg rounded-lg p-4 z-50">
    <div class="flex justify-between items-center mb-2">
      <h2 class="font-bold">Message {{ messageStore.activeUser?.name }}</h2>
      <button @click="close" class="text-gray-500 hover:text-black">✕</button>
    </div>
    <!-- You can add your conversation UI here -->
    <textarea class="w-full border rounded p-2 mb-2" placeholder="Type your message..."></textarea>
    <button class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">Send</button>
  </div>
</template>
