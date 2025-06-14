<script setup>
import { computed } from 'vue';

const props = defineProps({
    conversations: Array,
    activeConversation: Object,
});

const emit = defineEmits(['select', 'start-chat']);

const filteredConversations = computed(() => {
    return props.conversations.filter(conv => conv.latest_message || conv.type === 'group');
});
</script>

<template>
    <div class="p-4">
        <h3 class="font-semibold text-lg mb-4">Conversations</h3>
        
        <div class="space-y-2">
            <div 
                v-for="conversation in filteredConversations"
                :key="conversation.id"
                @click="emit('select', conversation)"
                :class="[
                    'flex items-center p-3 rounded-lg cursor-pointer',
                    activeConversation?.id === conversation.id ? 'bg-blue-100' : 'hover:bg-gray-100'
                ]"
            >
                <div class="flex-shrink-0 mr-3">
                    <img 
                        v-if="conversation.profile_picture"
                        :src="conversation.profile_picture"
                        class="w-10 h-10 rounded-full"
                    />
                    <div v-else class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ conversation.name || conversation.participants[0].user.name }}
                    </p>
                    <p v-if="conversation.latest_message" class="text-xs text-gray-500 truncate">
                        {{ conversation.latest_message.sender.name }}: 
                        {{ conversation.latest_message.content || 'Attachment' }}
                    </p>
                </div>
                <div v-if="conversation.latest_message" class="text-xs text-gray-500">
                    {{ relativeTime(conversation.latest_message.created_at) }}
                </div>
            </div>
        </div>
        
        <div class="mt-6">
            <h4 class="font-medium text-sm mb-2">Start New Chat</h4>
            <div class="space-y-2">
                <button 
                    v-for="user in $page.props.users"
                    :key="user.id"
                    @click="emit('start-chat', user.id)"
                    class="flex items-center w-full p-2 text-left hover:bg-gray-100 rounded-lg"
                >
                    <div class="w-8 h-8 rounded-full bg-gray-300 mr-2"></div>
                    <span class="text-sm">{{ user.name }}</span>
                </button>
            </div>
        </div>
    </div>
</template>