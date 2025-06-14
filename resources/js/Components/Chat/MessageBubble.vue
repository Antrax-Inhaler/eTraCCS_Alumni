<script setup>
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';

const props = defineProps({
    message: Object,
    isCurrentUser: Boolean,
    isGroup: Boolean,
});

const emit = defineEmits(['reply']);
</script>

<template>
    <div :class="['flex', isCurrentUser ? 'justify-end' : 'justify-start']">
        <div 
            :class="[
                'max-w-xs md:max-w-md lg:max-w-lg rounded-lg p-3',
                isCurrentUser ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-800'
            ]"
        >
            <!-- Group name header -->
            <div v-if="isGroup && !isCurrentUser" class="text-xs font-semibold mb-1">
                {{ message.sender.name }}
            </div>
            
            <!-- Reply context -->
            <div 
                v-if="message.reply_to_message_id" 
                :class="[
                    'text-xs p-2 mb-2 rounded border-l-4',
                    isCurrentUser ? 'border-blue-300 bg-blue-400 bg-opacity-30' : 'border-gray-300 bg-gray-200'
                ]"
            >
                <div class="font-semibold">
                    {{ message.replyTo?.sender_id === props.message.sender_id ? 'Replying to yourself' : `Replying to ${message.replyTo?.sender?.name}` }}
                </div>
                <div class="truncate">
                    {{ message.replyTo?.content?.substring(0, 50) }}{{ message.replyTo?.content?.length > 50 ? '...' : '' }}
                </div>
            </div>
            
            <!-- Message content -->
            <div class="whitespace-pre-wrap">{{ message.content }}</div>
            
            <!-- Attachments -->
            <div v-if="message.attachments?.length > 0" class="mt-2 space-y-2">
                <div v-for="attachment in message.attachments" :key="attachment.id">
                    <img 
                        v-if="attachment.file_type === 'image'" 
                        :src="`/storage/${attachment.file_path}`" 
                        class="max-w-full rounded"
                    />
                    <video 
                        v-else-if="attachment.file_type === 'video'" 
                        :src="`/storage/${attachment.file_path}`" 
                        controls
                        class="max-w-full rounded"
                    />
                    <a 
                        v-else 
                        :href="`/storage/${attachment.file_path}`" 
                        target="_blank"
                        class="text-blue-500 hover:underline flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Download file
                    </a>
                </div>
            </div>
            
            <!-- Message footer -->
            <div class="flex justify-between items-center mt-1">
                <div class="text-xs opacity-70">
                    {{ relativeTime(message.created_at) }}
                </div>
                <div class="flex space-x-1">
                    <button 
                        @click="emit('reply', message)"
                        class="text-xs opacity-70 hover:opacity-100"
                        :class="isCurrentUser ? 'text-blue-200' : 'text-gray-500'"
                    >
                        Reply
                    </button>
                    <div v-if="isCurrentUser" class="text-xs">
                        <span v-if="message.status === 'sent'">✓</span>
                        <span v-else-if="message.status === 'delivered'">✓✓</span>
                        <span v-else-if="message.status === 'seen'">✓✓ (seen)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>