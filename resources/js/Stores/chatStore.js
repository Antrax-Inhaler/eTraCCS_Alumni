// resources/js/Stores/chatStore.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useChatStore = defineStore('chat', () => {
    const openChats = ref([]); // List of open chat users

    function openChat(user) {
        // Prevent opening the same chat multiple times
        if (!openChats.value.find(chat => chat.id === user.id)) {
            openChats.value.push(user);
        }
    }

    function closeChat(userId) {
        openChats.value = openChats.value.filter(chat => chat.id !== userId);
    }

    return {
        openChats,
        openChat,
        closeChat,
    };
});
