<template>
    <div class="chat-container" :style="{ right: `${offset}px`, bottom: '16px' }">
        <!-- Header with user info and controls -->
        <div class="chat-header">
            <div class="user-info">
                <div class="avatar-container">
<img :src="user.profile_photo_url" :alt="user.full_name" class="user-avatar">
                <span class="status-indicator" :class="user.is_online ? 'online' : 'offline'" 
                :title="user.is_online ? 'Online now' : 'Last seen ago'"></span>
                </div>
                
                <div>
                    <span class="user-name-chat">{{ user.full_name }}</span>
                    
                </div>
            </div>
           
            <div class="chat-controls">
                <button @click="goToFullChat" class="control-button" title="Full chat">
                    <i class="fas fa-expand"></i>
                </button>
                <button @click="chatStore.closeChat(user.id)" class="control-button close-button" title="Close chat">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages" ref="chatContainer">
            <div v-for="message in messages" :key="message.id"
                 :class="['message', message.is_current_user ? 'message-right' : 'message-left']"
                 :title="formatMessageDate(message.created_at)">
                <!-- Only show avatar for other users -->
                                          <!-- <span v-if="!message.is_current_user" class="status-indicator" 
              :class="user.is_online ? 'online' : 'offline'"
              :title="user.is_online ? 'Online now' : 'Offline'">
        </span> -->
                <img v-if="!message.is_current_user" 
                     :src="message.sender_profile_photo_url || user.profile_photo_url" 
                     :alt="message.sender_name" 
                     class="message-avatar">

                <div class="message-content">
                    <!-- Only show name for other users -->
                    <!-- <strong v-if="!message.is_current_user" class="sender-name">
                        {{ message.sender_name }}
                    </strong> -->
                    <div class="message-text" v-if="message.content && message.content.trim()">
                        {{ message.content }}
                   </div>
                    
                    <!-- Attachment display -->
                    <div v-if="message.attachments && message.attachments.length" class="message-attachments">
                        <div v-for="(attachment, index) in message.attachments" :key="index" class="attachment">
                            <!-- Media Preview -->
                            <template v-if="isImage(attachment.file_path)">
                                <img :src="attachment.file_path" class="media-preview" @click="openInNewTab(attachment.file_path)" />
                            </template>
                            <template v-else-if="isVideo(attachment.file_path)">
                                <video class="media-preview" controls>
                                    <source :src="attachment.file_path" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </template>
                            <template v-else>
                                <div class="document-attachment">
                                    <i :class="getFileIcon(attachment.file_path)"></i>
                                    <a :href="attachment.file_path" target="_blank" class="attachment-link" :title="getFileName(attachment.file_path)">
                                        {{ truncateFileName(getFileName(attachment.file_path)) }}
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Status display -->
                    <div class="message-meta">
                        <!-- <span class="message-time">{{ formatMessageTime(message.created_at) }}</span> -->
                        <span v-if="message.is_current_user" class="message-status">
                            <div v-if="message.status === 'sent'"  title="Sent">sent</div>
                            <div v-else-if="message.status === 'delivered'" title="Delivered">delivered</div>
                            <div v-else-if="message.status === 'seen'" class="seen" title="Seen">seen</div>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Box and Attachments -->
        <div class="chat-input-container">
            <!-- Display selected attachments -->
            <div v-if="attachments.length" class="selected-attachments">
                <div v-for="(file, index) in attachments" :key="index" class="attachment-preview">
                    <i :class="getFileIcon(file.name)" class="file-icon"></i>
                    <span class="attachment-name" :title="file.name">{{ truncateFileName(file.name) }}</span>
                    <button @click="removeAttachment(index)" class="remove-attachment" title="Remove attachment">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="input-group">
                <button @click="fileInput.click()" class="attachment-button" title="Attach file">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="file" multiple @change="handleFileUpload" class="hidden" ref="fileInput" />
                
                <input
                    v-model="newMessage"
                    @keyup.enter="sendMessage"
                    type="text"
                    class="message-input"
                    placeholder="Type a message..."
                />
                
                <button @click="sendMessage" class="send-button" :disabled="!canSend" title="Send message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from 'vue';
import axios from 'axios';
import { useChatStore } from '@/Stores/chatStore';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';

// Extend dayjs with plugins
dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);

const props = defineProps({
    user: Object,
    index: Number,
    authUserId: Number, 
    isOnline: Boolean
});

let lastMessageId = ref(null);
let pollingInterval;
const chatStore = useChatStore();

const offset = computed(() => 16 + (props.index * 340)); // Stagger boxes

const messages = ref([]);
const newMessage = ref('');
const attachments = ref([]);
const chatContainer = ref(null);
const fileInput = ref(null);
const sentMessageIds = ref([]);
const messageStatuses = ref({}); // { messageId: 'delivered' }

// Interval handles
let fetchInterval = null;
let statusCheckInterval = null;
const canSend = computed(() => {
    return newMessage.value.trim().length > 0 || attachments.value.length > 0;
});

const isCurrentUser = (senderId) => {
    return senderId === props.authUserId;
};

function formatMessageDate(date) {
    return dayjs(date).format('LLLL');
}

function formatMessageTime(date) {
    return dayjs(date).format('LT');
}

function isImage(filePath) {
    return /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(filePath);
}

function isVideo(filePath) {
    return /\.(mp4|webm|ogg)$/i.test(filePath);
}

function getFileIcon(filePath) {
    const fileName = typeof filePath === 'string' ? filePath : filePath.name;
    const extension = fileName.split('.').pop().toLowerCase();
    
    switch(extension) {
        case 'pdf':
            return 'fas fa-file-pdf pdf-icon';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word word-icon';
        case 'xls':
        case 'xlsx':
            return 'fas fa-file-excel excel-icon';
        case 'ppt':
        case 'pptx':
            return 'fas fa-file-powerpoint ppt-icon';
        case 'zip':
        case 'rar':
        case '7z':
            return 'fas fa-file-archive archive-icon';
        case 'txt':
            return 'fas fa-file-alt text-icon';
        default:
            return 'fas fa-file file-icon';
    }
}

function getFileName(filePath) {
    return filePath.split('/').pop();
}

function truncateFileName(fileName, maxLength = 20) {
    if (fileName.length <= maxLength) return fileName;
    return fileName.substring(0, maxLength) + '...';
}

function openInNewTab(url) {
    window.open(url, '_blank');
}

function processAttachments(attachments) {
    const baseURL = window.location.origin + '/storage/';
    return (attachments || []).map(att => {
        return {
            ...att,
            file_path: att.file_path.startsWith('http') ? att.file_path : baseURL + att.file_path
        };
    });
}

async function fetchMessages() {
    try {
        const params = lastMessageId.value ? { params: { last_id: lastMessageId.value } } : {};
        const response = await axios.get(route('chat.messages', props.user.id), params);
        const newMessages = response.data.messages || [];

        if (newMessages.length) {
            const existingIds = new Set(messages.value.map(m => m.id));
            const filteredMessages = newMessages.filter(msg => !existingIds.has(msg.id));

            if (filteredMessages.length > 0) {
                messages.value.push(...filteredMessages);
                lastMessageId.value = filteredMessages[filteredMessages.length - 1].id;
                scrollToBottom();

                const hasIncoming = filteredMessages.some(msg => !msg.is_current_user);
                if (hasIncoming) {
                    await markAsSeen();
                }
            } else {
                // Merge status updates for existing messages
                for (const msg of newMessages) {
                    const index = messages.value.findIndex(m => m.id === msg.id);
                    if (index !== -1 && messages.value[index].status !== msg.status) {
                        messages.value[index].status = msg.status;
                        messageStatuses.value[msg.id] = msg.status;
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
}
async function markAsSeen() {
    try {
        await axios.post(route('chat.markAsSeen', props.user.id));
    } catch (e) {
        console.error('Failed to mark messages as seen', e);
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
}

function handleFileUpload(event) {
    attachments.value = Array.from(event.target.files);
}

function removeAttachment(index) {
    attachments.value.splice(index, 1);
}

async function sendMessage() {
    if (!canSend.value) return;

    const formData = new FormData();
    formData.append('message', newMessage.value);

    attachments.value.forEach((file, i) => {
        formData.append(`attachments[${i}]`, file);
    });

    try {
        const response = await axios.post(route('chat.send', props.user.id), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        const processedAttachments = processAttachments(response.data.attachments);

        messages.value.push({
            ...response.data,
            attachments: processedAttachments,
            sender_profile_photo_url: props.authUserId === response.data.sender_id 
                ? chatStore.authUser.profile_photo_url 
                : props.user.profile_photo_url,
            sender_name: props.authUserId === response.data.sender_id 
                ? chatStore.authUser.full_name 
                : props.user.full_name,
            is_current_user: true,
            status: response.data.status || 'sent', // Ensure status is included
        });

        newMessage.value = '';
        attachments.value = [];
        if (fileInput.value) fileInput.value.value = '';
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
    }
}

function goToFullChat() {
    window.open(route('chat.start', props.user.id), '_blank');
}

// async function markAsSeen() {
//     try {
//         await axios.post(route('chat.markAsSeen', props.user.id));
//     } catch (e) {
//         console.error('Failed to mark messages as seen', e);
//     }
// }

onMounted(async () => {
    await fetchMessages(); // Initial load
    if (messages.value.length) {
        lastMessageId.value = messages.value[messages.value.length - 1].id;
    }

    fetchInterval = setInterval(fetchMessages, 3000); // Every 3 sec
    statusCheckInterval = setInterval(checkMessageStatuses, 5000); // Every 5 sec
});

onBeforeUnmount(() => {
    clearInterval(fetchInterval);
    clearInterval(statusCheckInterval);
});
onBeforeUnmount(() => {
    clearInterval(pollingInterval);
});
</script>


<style scoped>
.chat-container {
    position: fixed;
    width: 320px;
    height: 420px;
    display: flex;
    flex-direction: column;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    z-index: 50;
    transition: all 0.3s ease;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: rgba(40, 40, 40, 0.9);
    border-bottom: 1px solid var(--card-border);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name-chat-chat {
    font-weight: 500;
    color: var(--text-primary);
    font-size: 14px;
}

.user-status {
    font-size: 11px;
    color: #4CAF50;
}

.user-status.offline {
    color: var(--text-secondary);
}

.chat-controls {
    display: flex;
    gap: 8px;
}

.control-button {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 14px;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
}

.control-button:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.1);
}

.close-button:hover {
    color: #ff5252;
}

.chat-messages {
    flex: 1;
    padding: 12px 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.message {
    display: flex;
    max-width: 80%;
    gap: 8px;
}

.message-left {
    align-self: flex-start;
}

.message-right {
    margin-left: auto;
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    align-self: flex-end;
    margin-bottom: 18px;
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.message-right .message-content {
    align-items: flex-end;
}
.media-preview {
    max-width: 100%;
    max-height: 180px;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s;
}

.media-preview:hover {
    transform: scale(1.02);
}

.sender-name {
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 2px;
}

.message-text {
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.4;
    word-break: break-word;
}

.message-right .message-text {
    background: var(--primary);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-left .message-text {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border-bottom-left-radius: 4px;
}

.message-attachments {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 4px;
}

.attachment {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.attachment-link {
    color: var(--primary);
    text-decoration: none;
    transition: color 0.2s;
}

.attachment-link:hover {
    text-decoration: underline;
}

.message-right .attachment-link {
    color: rgba(255, 255, 255, 0.8);
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}

.message-time {
    opacity: 0.7;
}

.message-status {
    display: flex;
    align-items: center;
}

.message-right .message-meta {
    color: rgba(255, 255, 255, 0.7);
}

.message-left .message-meta {
    color: var(--text-secondary);
}

.seen {
    color: #4CAF50;
}

.chat-input-container {
    padding: 12px 16px;
    border-top: 1px solid var(--card-border);
    background: rgba(40, 40, 40, 0.9);
}

.selected-attachments {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}

.attachment-preview {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.attachment-name {
    max-width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.remove-attachment {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    margin-left: 6px;
    font-size: 10px;
    padding: 0;
}

.input-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.message-input {
    flex: 1;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 8px 16px;
    color: var(--text-primary);
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
}

.message-input:focus {
    border-color: var(--primary);
}

.attachment-button, .send-button {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
}

.attachment-button:hover, .send-button:hover {
    background: rgba(255, 255, 255, 0.2);
    color: var(--text-primary);
}

.send-button {
    background: var(--primary);
    color: white;
}

.send-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.send-button:not(:disabled):hover {
    background: #ff9e2c;
}

/* Scrollbar styling */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.chat-messages::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
.document-attachment {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    max-width: 100%;
}

.message-right .document-attachment {
    background: rgba(255, 255, 255, 0.2);
}

.file-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.pdf-icon {
    color: #ff5252;
}

.word-icon {
    color: #2b579a;
}

.excel-icon {
    color: #217346;
}

.ppt-icon {
    color: #d24726;
}

.archive-icon {
    color: #ff9800;
}

.text-icon {
    color: #607d8b;
}

/* Attachment name with ellipsis */
.attachment-link {
    color: var(--primary);
    text-decoration: none;
    transition: color 0.2s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
    display: inline-block;
}

.message-right .attachment-link {
    color: rgba(255, 255, 255, 0.8);
}

.attachment-link:hover {
    text-decoration: underline;
}

/* Selected attachments preview */
.attachment-preview {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    max-width: 100%;
}

.attachment-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
    margin: 0 6px;
}

.hidden {
    display: none;
}
.offline{
    background-color: transparent;
}
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    position: absolute;
    bottom: -2px;
    right: -2px;
}
.status-indicator.online {
    border: none;
}
/* Mobile-specific styles */
@media (max-width: 768px) {
  /* Container adjustments */
  .chat-container {
width: calc(100vw - 32px) !important;
        height: 70vh !important;
        max-height: 70vh !important;
        right: 16px !important;
        bottom: 65px !important;
        left: 16px !important;
        transform: translateX(0);
  }

  /* Chat switcher for mobile */
  .chat-header {
    position: relative;
  }

  .chat-switcher {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: var(--text-primary);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }
  
  /* Chat list overlay */
  .chat-list-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 100;
    display: flex;
    flex-direction: column;
    padding: 16px;
  }

  .chat-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    color: white;
  }

  .chat-list-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
  }

  .chat-list-item {
    display: flex;
    align-items: center;
    padding: 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    margin-bottom: 8px;
    color: white;
  }

  .chat-list-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 12px;
  }

  /* Message adjustments */
  .message {
    max-width: 90% !important;
  }

  .message-avatar {
    width: 24px !important;
    height: 24px !important;
  }

  .message-text {
    font-size: 13px !important;
    padding: 6px 10px !important;
  }

  /* Input adjustments */
  .chat-input-container {
    padding: 8px 12px !important;
  }

  .message-input {
    padding: 6px 12px !important;
    font-size: 13px !important;
  }

  .attachment-button, .send-button {
    width: 32px !important;
    height: 32px !important;
    font-size: 14px !important;
  }

  /* Hide desktop-specific elements if any */
  .desktop-only {
    display: none !important;
  }
}

/* For very small screens */
@media (max-width: 480px) {
  .chat-container {
height: 83vh !important;
        max-height: 90vh !important;
  }

  .user-name-chat {
    font-size: 13px !important;
    max-width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .chat-messages {
    padding: 8px 12px !important;
  }
}
</style>

