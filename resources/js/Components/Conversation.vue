<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MessageBubble from '@/Components/Chat/MessageBubble.vue';
import AttachmentPreview from '@/Components/Chat/AttachmentPreview.vue';
import ParticipantInfo from '@/Components/Chat/ParticipantInfo.vue';

const props = defineProps({
    conversation: Object,
    messages: Object,
});

const page = usePage();
const currentUser = ref(page.props.auth.user);
const messageContent = ref('');
const attachments = ref([]);
const previews = ref([]);
const messages = ref(props.messages.data.reverse());
const lastMessageId = ref(props.messages.data.length ? props.messages.data[props.messages.data.length - 1].id : 0);
const participants = ref(props.conversation.participants);
const isLoading = ref(false);
const longPolling = ref(null);

// Format educational background for display
const formatEducation = (participant) => {
    if (!participant.college) return 'No education info';
    
    let text = `${participant.college}`;
    if (participant.year_graduated) {
        text += `, ${participant.year_graduated}`;
    }
    if (participant.currently_studying) {
        text += ' (Currently studying)';
    }
    if (participant.is_graduate_study) {
        text += ' (Graduate studies)';
    }
    return text;
};

// Handle file selection
const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    attachments.value = files;
    
    previews.value = files.map(file => {
        const url = URL.createObjectURL(file);
        let type = 'document';
        if (file.type.startsWith('image/')) type = 'image';
        if (file.type.startsWith('video/')) type = 'video';
        
        return { url, type, name: file.name };
    });
};

// Remove attachment
const removeAttachment = (index) => {
    attachments.value.splice(index, 1);
    previews.value.splice(index, 1);
};

// Send message
const sendMessage = async () => {
    if (!messageContent.value && !attachments.value.length) return;
    
    const formData = new FormData();
    formData.append('conversation_id', props.conversation.id);
    if (messageContent.value) formData.append('content', messageContent.value);
    
    attachments.value.forEach(file => {
        formData.append('attachments[]', file);
    });
    
    try {
        const response = await axios.post(route('chat.send'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        
        if (response.data.success) {
            messages.value.push(response.data.message);
            messageContent.value = '';
            attachments.value = [];
            previews.value = [];
            scrollToBottom();
        }
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

// Long polling for new messages
const startLongPolling = async () => {
    const poll = async () => {
        try {
            const response = await axios.post(route('chat.long-poll'), {
                conversation_id: props.conversation.id,
                last_message_id: lastMessageId.value,
            });
            
            if (response.data.messages.length) {
                messages.value.push(...response.data.messages);
                lastMessageId.value = response.data.last_message_id;
                scrollToBottom();
            }
        } catch (error) {
            console.error('Long poll error:', error);
        } finally {
            longPolling.value = setTimeout(poll, 1000);
        }
    };
    
    poll();
};

const scrollToBottom = () => {
    nextTick(() => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
    startLongPolling();
});

onUnmounted(() => {
    if (longPolling.value) {
        clearTimeout(longPolling.value);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="conversation.name || 'Conversation'" />
        
        <div class="flex h-[calc(100vh-64px)]">
            <!-- Sidebar with participants -->
            <div class="w-64 border-r bg-white p-4 hidden md:block">
                <h3 class="font-semibold text-lg mb-4">Participants</h3>
                <ul class="space-y-3">
                    <li v-for="participant in participants" :key="participant.id" class="flex items-center space-x-3">
                        <img :src="participant.avatar" class="w-10 h-10 rounded-full" />
                        <div>
                            <p class="font-medium">{{ participant.name }}</p>
                            <p class="text-xs text-gray-500">{{ formatEducation(participant) }}</p>
                        </div>
                    </li>
                </ul>
            </div>
            
            <!-- Main chat area -->
            <div class="flex-1 flex flex-col">
                <!-- Chat header -->
                <div class="border-b p-4 flex items-center justify-between bg-white">
                    <div class="flex items-center space-x-3">
                        <img v-if="conversation.profile_picture" :src="conversation.profile_picture" class="w-10 h-10 rounded-full" />
                        <div>
                            <h2 class="font-semibold">{{ conversation.name || participants.filter(p => p.id !== currentUser.id).map(p => p.name).join(', ') }}</h2>
                            <p class="text-xs text-gray-500">
                                {{ conversation.type === 'personal' ? 'Direct message' : 'Group chat' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Messages container -->
                <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                    <div v-for="message in messages" :key="message.id">
                        <MessageBubble 
                            :message="message" 
                            :is-current-user="message.sender.id === currentUser.id" 
                        />
                    </div>
                </div>
                
                <!-- Message input -->
                <div class="border-t p-4 bg-white">
                    <div v-if="previews.length" class="mb-3 flex space-x-2 overflow-x-auto pb-2">
                        <AttachmentPreview 
                            v-for="(preview, index) in previews" 
                            :key="index"
                            :preview="preview"
                            @remove="removeAttachment(index)"
                        />
                    </div>
                    
                    <div class="flex space-x-2">
                        <input 
                            type="file" 
                            multiple 
                            @change="handleFileChange" 
                            class="hidden" 
                            id="file-input" 
                            accept="image/*,video/*,.pdf,.doc,.docx"
                        />
                        <button 
                            @click="document.getElementById('file-input').click()" 
                            class="p-2 rounded-full hover:bg-gray-100"
                        >
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </button>
                        
                        <input 
                            v-model="messageContent" 
                            @keyup.enter="sendMessage" 
                            placeholder="Type a message..." 
                            class="flex-1 border rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        
                        <button 
                            @click="sendMessage" 
                            class="p-2 rounded-full bg-blue-500 text-white hover:bg-blue-600"
                            :disabled="!messageContent && !attachments.length"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>