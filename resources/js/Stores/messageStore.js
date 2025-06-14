import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useMessageStore = defineStore('message', () => {
  const isOpen = ref(false);
  const activeUser = ref(null);

  function openMessage(user) {
    activeUser.value = user;
    isOpen.value = true;
  }

  function closeMessage() {
    isOpen.value = false;
    activeUser.value = null;
  }

  return { isOpen, activeUser, openMessage, closeMessage };
});
