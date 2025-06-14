<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: Object,
    isCurrentUser: Boolean,
});

const formattedDate = computed(() => {
    return new Date(props.message.created_at).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
});
</script>

<template>
    <div :class="['flex', isCurrentUser ? 'justify-end' : 'justify-start']">
        <div 
            :class="[
                'max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl rounded-lg px-4 py-2',
                isCurrentUser ? 'bg-blue-500 text-white' : 'bg-white border'
            ]"
        >
            <div v-if="!isCurrentUser" class="text-xs font-semibold text-gray-500 mb-1">
                {{ message.sender.name }}
            </div>
            
            <p v-if="message.content" class="whitespace-pre-wrap">{{ message.content }}</p>
            
            <div v-if="message.attachments.length" class="mt-2 space-y-2">
                <div v-for="attachment in message.attachments" :key="attachment.id">
                    <img 
                        v-if="attachment.type === 'image'" 
                        :src="attachment.url" 
                        class="max-w-full rounded-lg"
                    />
                    
                    <video 
                        v-else-if="attachment.type === 'video'" 
                        :src="attachment.url" 
                        controls 
                        class="max-w-full rounded-lg"
                    ></video>
                    
                    <a 
                        v-else 
                        :href="attachment.url" 
                        target="_blank" 
                        class="inline-flex items-center text-blue-500 hover:underline"
                    >
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        View Document
                    </a>
                </div>
            </div>
            
            <div class="text-xs mt-1 flex items-center justify-end space-x-1">
                <span>{{ formattedDate }}</span>
                <span v-if="isCurrentUser">
                    <svg v-if="message.status === 'sent'" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                    </svg>
                    <svg v-else-if="message.status === 'seen'" class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" />
                    </svg>
                </span>
            </div>
        </div>
    </div>
</template>