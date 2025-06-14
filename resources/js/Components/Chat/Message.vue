<script setup>
import { computed } from 'vue';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';

const props = defineProps({
    message: Object,
    isCurrentUser: Boolean,
});

const emit = defineEmits(['reply']);

const messageClasses = computed(() => {
    return [
        'flex',
        'max-w-xs',
        'md:max-w-md',
        'lg:max-w-lg',
        'rounded-lg',
        'p-3',
        props.isCurrentUser ? 'bg-blue-500 text-white ml-auto' : 'bg-gray-200 text-gray-800 mr-auto',
    ];
});

const timeClasses = computed(() => {
    return [
        'text-xs',
        'mt-1',
        props.isCurrentUser ? 'text-blue-100' : 'text-gray-500',
    ];
});
</script>

<template>
    <div :class="messageClasses">
        <div class="flex flex-col">
            <div v-if="message.reply_to_message" class="mb-2 p-2 bg-black bg-opacity-10 rounded">
                <div class="text-xs font-semibold">
                    Replying to {{ message.reply_to_message.sender.name }}
                </div>
                <div class="text-xs truncate">
                    {{ message.reply_to_message.content || 'Attachment' }}
                </div>
            </div>
            
            <div v-if="message.content" class="break-words">
                {{ message.content }}
            </div>
            
            <div v-if="message.attachments && message.attachments.length > 0" class="mt-2 space-y-2">
                <div v-for="attachment in message.attachments" :key="attachment.id">
                    <img 
                        v-if="attachment.file_type === 'image'"
                        :src="'/storage/' + attachment.file_path"
                        class="max-h-48 rounded"
                        alt="Attachment"
                    />
                    <video 
                        v-else-if="attachment.file_type === 'video'"
                        :src="'/storage/' + attachment.file_path"
                        controls
                        class="max-h-48 rounded"
                    ></video>
                    <a 
                        v-else
                        :href="'/storage/' + attachment.file_path"
                        target="_blank"
                        class="flex items-center text-blue-500 hover:underline"
                    >
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Document
                    </a>
                </div>
            </div>
            
            <div :class="timeClasses">
                {{ relativeTime(message.created_at) }}
                <button 
                    v-if="!isCurrentUser"
                    @click="emit('reply', message)"
                    class="ml-2 hover:text-blue-300"
                    title="Reply"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>