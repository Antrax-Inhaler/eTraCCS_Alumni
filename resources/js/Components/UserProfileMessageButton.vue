<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    userId: {
        type: Number,
        required: true,
    },
});

const isLoading = ref(false);

const startConversation = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post(route('chat.create-direct'), {
            recipient_id: props.userId,
        });
        
        router.visit(route('chat.show', {
            conversation: response.data.conversation_id,
        }));
    } catch (error) {
        console.error('Error creating conversation:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <button 
        @click="startConversation" 
        :disabled="isLoading"
        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 flex items-center space-x-2"
    >
        <svg v-if="isLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Message</span>
    </button>
</template>