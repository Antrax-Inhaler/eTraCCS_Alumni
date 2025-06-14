<template>
  <div class="chatbox">
    <!-- Chat Header -->
    <div class="chatbox-header">
      <span>{{ chatUser.name }}</span>
      <button @click="navigateToFullChat" class="go-fullchat-button">
        <i class="fas fa-external-link-alt"></i> Full Chat
      </button>
    </div>

    <!-- Chat Messages -->
    <div class="chatbox-messages">
      <!-- messages here -->
    </div>

    <!-- Chat Input -->
    <div class="chatbox-input">
      <!-- input and send button here -->
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  chatUser: Object,
});

function navigateToFullChat() {
  router.get(route('chat.start', props.chatUser.id));
}
</script>

<style scoped>
.go-fullchat-button {
  background-color: #4caf50;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

.go-fullchat-button i {
  margin-right: 5px;
}
</style>
