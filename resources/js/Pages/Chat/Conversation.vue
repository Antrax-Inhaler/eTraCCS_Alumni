<template>
  <div class="conversation-container">
    <!-- Chat Header -->
    <div class="chat-header">
      <div class="header-left">
        <Link :href="route('chat.index')" class="back-button">
          <i class="fas fa-arrow-left"></i>
        </Link>
        
        <div class="user-info">
          <div class="avatar-container">
            <img :src="conversation.avatar || defaultAvatar" :alt="conversation.name" class="avatar">
            <span v-if="isOnline" class="online-indicator"></span>
          </div>
          
          <div>
            <h3>{{ conversation.name }}</h3>
            <p v-if="conversation.type === 'personal'" class="user-status">
              {{ isOnline ? 'Online' : 'Offline' }}
            </p>
            <p v-else class="user-status">
              {{ conversation.participants.length }} members
            </p>
          </div>
        </div>
      </div>
      
      <div class="header-right">
        <button v-if="conversation.type === 'group'" @click="showGroupInfo = true" class="header-button">
          <i class="fas fa-info-circle"></i>
        </button>
        <button @click="showOptions = !showOptions" class="header-button">
          <i class="fas fa-ellipsis-v"></i>
        </button>
        
        <div v-if="showOptions" class="options-dropdown">
          <button @click="clearConversation" class="dropdown-item">
            <i class="fas fa-trash"></i> Clear chat
          </button>
          <button v-if="conversation.type === 'group' && isAdmin" 
                  @click="showGroupSettings = true" 
                  class="dropdown-item">
            <i class="fas fa-cog"></i> Group settings
          </button>
          <button v-if="conversation.type === 'group'" @click="leaveGroup" class="dropdown-item">
            <i class="fas fa-sign-out-alt"></i> Leave group
          </button>
        </div>
      </div>
    </div>
    
    <!-- Chat Messages -->
    <div class="chat-messages" ref="messagesContainer">
      <div v-if="loadingMessages" class="loading-messages">
        <i class="fas fa-spinner fa-spin"></i> Loading messages...
      </div>
      
      <div v-for="message in messages" :key="message.id" 
           :class="['message', message.is_current_user ? 'outgoing' : 'incoming']">
        <div v-if="!message.is_current_user" class="message-avatar">
          <img :src="message.sender_profile_photo_url" :alt="message.sender_name" class="avatar">
        </div>
        
        <div class="message-content">
          <div v-if="!message.is_current_user && conversation.type === 'group'" class="sender-name">
            {{ message.sender_name }}
          </div>
          
          <div class="message-bubble">
            <div v-if="message.content" class="message-text">{{ message.content }}</div>
            
            <!-- Attachments -->
            <div v-if="message.attachments && message.attachments.length" class="message-attachments">
              <div v-for="(attachment, index) in message.attachments" :key="index" class="attachment">
                <!-- Image preview -->
                <template v-if="isImage(attachment.file_path)">
                  <img :src="attachment.file_path" class="attachment-preview" 
                       @click="openAttachment(attachment.file_path)">
                </template>
                
                <!-- Video preview -->
                <template v-else-if="isVideo(attachment.file_path)">
                  <video class="attachment-preview" controls>
                    <source :src="attachment.file_path" type="video/mp4">
                    Your browser does not support the video tag.
                  </video>
                </template>
                
                <!-- Document download -->
                <template v-else>
                  <a :href="attachment.file_path" target="_blank" class="document-attachment">
                    <i :class="getFileIcon(attachment.file_path)"></i>
                    <span>{{ getFileName(attachment.file_path) }}</span>
                  </a>
                </template>
              </div>
            </div>
            
            <div class="message-meta">
              <span class="message-time">{{ formatTime(message.created_at) }}</span>
              <span v-if="message.is_current_user" class="message-status">
                <i v-if="message.status === 'sent'" class="fas fa-check"></i>
                <i v-if="message.status === 'delivered'" class="fas fa-check-double"></i>
                <i v-if="message.status === 'seen'" class="fas fa-check-double seen"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Typing Indicator -->
    <!-- <div v-if="typingUsers.length > 0" class="typing-indicator">
      <div v-for="user in typingUsers" :key="user.id" class="typing-user">
        <img :src="user.avatar" :alt="user.name" class="typing-avatar">
        <div class="typing-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </div> -->
    
    <!-- Chat Input -->
    <div class="chat-input">
      <div v-if="attachments.length" class="attachments-preview">
        <div v-for="(file, index) in attachments" :key="index" class="attachment-item">
          <i :class="getFileIcon(file.name)"></i>
          <span class="file-name">{{ truncateFileName(file.name) }}</span>
          <button @click="removeAttachment(index)" class="remove-attachment">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="input-container">
        <button @click="toggleEmojiPicker" class="input-button">
          <i class="far fa-smile"></i>
        </button>
        
        <button @click="openFilePicker" class="input-button">
          <i class="fas fa-paperclip"></i>
          <input type="file" ref="fileInput" multiple @change="handleFileUpload" class="file-input">
        </button>
        
        <input v-model="newMessage" 
               @keyup.enter="sendMessage" 
               @input="handleTyping"
               type="text" 
               placeholder="Type a message..." 
               class="message-input">
        
        <button @click="sendMessage" :disabled="!canSend" class="send-button">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    </div>
    
    <!-- Emoji Picker -->
    <EmojiPicker 
      v-if="showEmojiPicker" 
      @emoji-selected="addEmoji"
      @close="showEmojiPicker = false"
    />
    
    <!-- Group Info Modal -->
    <GroupInfoModal 
      v-if="showGroupInfo" 
      :conversation="conversation"
      @close="showGroupInfo = false"
      @add-members="showAddMembersModal = true"
    />
    
    <!-- Group Settings Modal -->
    <GroupSettingsModal 
      v-if="showGroupSettings" 
      :conversation="conversation"
      @close="showGroupSettings = false"
      @update="handleGroupUpdate"
    />
    
    <!-- Add Members Modal -->
    <AddMembersModal 
      v-if="showAddMembersModal" 
      :current-members="conversation.participants.map(p => p.id)"
      @close="showAddMembersModal = false"
      @add-members="addGroupMembers"
    />
    
    <!-- Attachment Viewer -->
    <AttachmentViewer 
      v-if="selectedAttachment" 
      :src="selectedAttachment"
      @close="selectedAttachment = null"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import EmojiPicker from '@/Components/Chat/EmojiPicker.vue';
import GroupInfoModal from '@/Components/Chat/GroupInfoModal.vue';
import GroupSettingsModal from '@/Components/Chat/GroupSettingsModal.vue';
import AddMembersModal from '@/Components/Chat/AddMembersModal.vue';
import AttachmentViewer from '@/Components/Chat/AttachmentViewer.vue';

// Extend dayjs with plugins
dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);

const props = defineProps({
  conversation: Object,
  currentUser: Object,
      authUserId: Number, 

});

// Refs
const messages = ref([]);
const newMessage = ref('');
const attachments = ref([]);
const fileInput = ref(null);
const messagesContainer = ref(null);
const loadingMessages = ref(true);
const lastMessageId = ref(null);
const showEmojiPicker = ref(false);
const showGroupInfo = ref(false);
const showGroupSettings = ref(false);
const showAddMembersModal = ref(false);
const selectedAttachment = ref(null);
const showOptions = ref(false);
const typingUsers = ref([]);
const typingTimeout = ref(null);
const defaultAvatar = ref('/images/default-avatar.png');
const pollingInterval = ref(null);
const typingPollingInterval = ref(null);

// Computed properties
// const isOnline = computed(() => {
//   if (props.conversation.type === 'personal') {
//     const participant = props.conversation.participants.find(p => p.id !== props.currentUser.id);
//     return participant?.is_online || false;
//   }
//   return false;
// });

const isAdmin = computed(() => {
  return props.conversation.created_by === props.currentUser.id;
});

const canSend = computed(() => {
  return newMessage.value.trim().length > 0 || attachments.value.length > 0;
});

// Methods
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
    case 'pdf': return 'fas fa-file-pdf pdf-icon';
    case 'doc': case 'docx': return 'fas fa-file-word word-icon';
    case 'xls': case 'xlsx': return 'fas fa-file-excel excel-icon';
    case 'ppt': case 'pptx': return 'fas fa-file-powerpoint ppt-icon';
    case 'zip': case 'rar': case '7z': return 'fas fa-file-archive archive-icon';
    case 'txt': return 'fas fa-file-alt text-icon';
    default: return 'fas fa-file file-icon';
  }
}

function getFileName(filePath) {
  return filePath.split('/').pop();
}

function truncateFileName(name, length = 20) {
  if (!name) return '';
  const extension = name.split('.').pop();
  const baseName = name.substring(0, name.lastIndexOf('.'));
  const truncatedBase = baseName.length > length ? baseName.substring(0, length) + '...' : baseName;
  return truncatedBase + (extension ? '.' + extension : '');
}

function formatTime(date) {
  return dayjs(date).format('h:mm A');
}

const getOtherParticipant = computed(() => {
  if (!getConversationId.value || props.conversation?.type !== 'personal') return null;
  const participant = props.conversation.participants?.find(p => p.id !== props.currentUser?.id);
  return participant ?? null;
});

async function fetchMessages() {
  if (!getConversationId.value) {
    console.error('No conversation ID available');
    return;
  }

  try {
    loadingMessages.value = true;
    
    // Determine the correct endpoint based on conversation type
    const endpoint = props.conversation.type === 'personal' 
      ? route('chat.messages', { user: getOtherParticipant.value?.id ?? 0 })
      : route('chat.conversation-messages', { conversation: getConversationId.value });
    
    // Only add last_id param if we have one
    const params = lastMessageId.value ? { params: { last_id: lastMessageId.value } } : {};
    
    const response = await axios.get(endpoint, params);
    
    const newMessages = response.data.messages || [];
    if (newMessages.length) {
      const existingIds = new Set(messages.value.map(m => m.id));
      const filteredMessages = newMessages.filter(msg => !existingIds.has(msg.id));
      
      if (filteredMessages.length > 0) {
        // Add new messages to the beginning (for infinite scroll)
        messages.value.unshift(...filteredMessages.reverse());
        lastMessageId.value = filteredMessages[0].id;
        
        // Scroll to bottom only if we're loading initial messages
        if (!lastMessageId.value) {
          scrollToBottom();
        }
      }
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
    // You might want to show an error message to the user here
  } finally {
    loadingMessages.value = false;
  }
}
async function checkForNewMessages() {
  if (!getConversationId.value) return;
  
  try {
    const endpoint = props.conversation.type === 'personal' 
      ? route('chat.messages', { user: getOtherParticipant.value?.id })
      : route('chat.conversation-messages', { conversation: props.conversation.id });
    
    const params = { last_id: messages.value[messages.value.length - 1]?.id || 0 };
    const response = await axios.get(endpoint, { params }); // wrap in { params }

    const newMessages = response.data.messages || [];
    if (newMessages.length) {
      const existingIds = new Set(messages.value.map(m => m.id));
      const filteredMessages = newMessages.filter(msg => !existingIds.has(msg.id));

      if (filteredMessages.length > 0) {
        messages.value.push(...filteredMessages);
        scrollToBottom();

        if (filteredMessages.some(msg => msg.sender_id !== props.currentUser.id)) {
          await markAsSeen();
        }
      }
    }
  } catch (error) {
    console.error('Error checking for new messages:', error);
  }
}

// async function checkTypingStatus() {
//     if (!getConversationId.value) return;
//   try {
//     const response = await axios.get(route('chat.typing-status', { 
//       conversation_id: props.conversation.id 
//     }));
    
//     // Update typing users list
//     typingUsers.value = response.data.typing_users || [];
    
//     // Clear any users who stopped typing
//     if (typingTimeout.value) {
//       clearTimeout(typingTimeout.value);
//     }
    
//     typingTimeout.value = setTimeout(() => {
//       typingUsers.value = [];
//     }, 2000);
//   } catch (error) {
//     console.error('Error checking typing status:', error);
//   }
// }
const isCurrentUser = (senderId) => {
    return senderId === props.authUserId;
};
function truncate(text, length) {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
}

function openFilePicker() {
  fileInput.value.click();
}

function handleFileUpload(event) {
  attachments.value = Array.from(event.target.files);
}

function removeAttachment(index) {
  attachments.value.splice(index, 1);
}

function openAttachment(url) {
  selectedAttachment.value = url;
}

async function sendMessage() {
  if (!canSend.value) return;

  const formData = new FormData();
  if (newMessage.value) {
    formData.append('message', newMessage.value);
  }

  attachments.value.forEach((file, i) => {
    formData.append(`attachments[${i}]`, file);
  });

  try {
    const endpoint = props.conversation.type === 'personal'
      ? route('chat.send', { user: getOtherParticipant.value?.id })
      : route('chat.conversation.send', { conversation: props.conversation.id });

    const response = await axios.post(endpoint, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    const processedAttachments = response.data.attachments || [];

    // Use fallback if currentUser is undefined
    const senderName = props.currentUser?.name || 'You';
    const senderPhoto = props.currentUser?.profile_photo_url || '/default.png';

    messages.value.push({
      ...response.data,
      attachments: processedAttachments,
      is_current_user: true,
      status: response.data.status || 'sent',
      sender_name: senderName,
      sender_profile_photo_url: senderPhoto,
    });

    // Reset input and attachments
    newMessage.value = '';
    attachments.value = [];
    if (fileInput.value) fileInput.value.value = '';

    scrollToBottom();

  } catch (error) {
    console.error('Error sending message:', error);
  }
}


async function markAsSeen() {
  try {
    const endpoint = props.conversation.type === 'personal' 
      ? route('chat.markAsSeen', { user: getOtherParticipant().id })
      : route('chat.conversation.markAsSeen', { conversation: props.conversation.id });
    
    await axios.post(endpoint);
  } catch (error) {
    console.error('Error marking messages as seen:', error);
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}


function toggleEmojiPicker() {
  showEmojiPicker.value = !showEmojiPicker.value;
}

function addEmoji(emoji) {
  newMessage.value += emoji;
  showEmojiPicker.value = false;
}

async function handleTyping() {
  if (newMessage.value.length > 0) {
    try {
      await axios.post(route('chat.typing', {
        conversation_id: props.conversation.id,
        user_id: props.currentUser.id,
        typing: true
      })); // ✅ closing ) was missing here
    } catch (error) {
      console.error('Error sending typing indicator:', error);
    }
  }
}
const getConversationId = computed(() => {
  // Add null checks for conversation
  return props.conversation?.id ?? null;
});


function clearConversation() {
  // TODO: Implement clear conversation logic
  showOptions.value = false;
}

function addGroupMembers(members) {
  // TODO: Implement add group members logic
  showAddMembersModal.value = false;
}

function handleGroupUpdate(updatedData) {
  // TODO: Implement group update logic
  showGroupSettings.value = false;
}

function leaveGroup() {
  // TODO: Implement leave group logic
  showOptions.value = false;
}

// Lifecycle hooks
onMounted(() => {
  if (!getConversationId.value) {
    console.error('No conversation ID available');
    return;
  }

  fetchMessages();
  
  pollingInterval.value = setInterval(checkForNewMessages, 3000);
//   typingPollingInterval.value = setInterval(checkTypingStatus, 1000);
  
  if (messagesContainer.value) {
    messagesContainer.value.addEventListener('scroll', () => {
      if (messagesContainer.value.scrollTop === 0 && !loadingMessages.value) {
        fetchMessages();
      }
    });
  }
});

onBeforeUnmount(() => {
  // Clean up intervals
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
  }
  if (typingPollingInterval.value) {
    clearInterval(typingPollingInterval.value);
  }
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
});

// Watch for scroll events to load more messages
watch(() => messagesContainer.value, (container) => {
  if (container) {
    container.addEventListener('scroll', () => {
      if (container.scrollTop === 0 && !loadingMessages.value) {
        fetchMessages();
      }
    });
  }
});
</script>

<style scoped>
.conversation-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: #f5f5f5;
}

.chat-header {
  padding: 12px 16px;
  background-color: #fff;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.back-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #616161;
  text-decoration: none;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-info h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 500;
}

.user-status {
  margin: 0;
  font-size: 13px;
  color: #757575;
}

.avatar-container {
  position: relative;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.online-indicator {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 10px;
  height: 10px;
  background-color: #4caf50;
  border-radius: 50%;
  border: 2px solid #fff;
}

.header-right {
  position: relative;
}

.header-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #616161;
  margin-left: 12px;
}

.options-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  background-color: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 10;
  min-width: 180px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  width: 100%;
  text-align: left;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
  color: #424242;
}

.dropdown-item:hover {
  background-color: #f5f5f5;
}

.chat-messages {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  background-color: #e5ddd5;
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAABnSURBVDhP7cxBCsAgDETR6P3P3KVLQfBfM0gXQjYv8Jgx5lprf5iZqspM7L33zcx8zrn3XmutlVJKKf0X3w8z01rLzCQiEBFmZowxUkqttZxzSqm1FhFmJiLMzJxzrbX3/gBfXg8j2TuFJAAAAABJRU5ErkJggg==');
}

.loading-messages {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 16px;
  color: #757575;
}

.message {
  display: flex;
  margin-bottom: 16px;
}

.message.incoming {
  justify-content: flex-start;
}

.message.outgoing {
  justify-content: flex-end;
}

.message-avatar .avatar {
  width: 32px;
  height: 32px;
}

.message-content {
  max-width: 70%;
}

.sender-name {
  font-size: 12px;
  color: #757575;
  margin-bottom: 4px;
}

.message-bubble {
  padding: 8px 12px;
  border-radius: 18px;
  position: relative;
}

.incoming .message-bubble {
  background-color: #fff;
  border-top-left-radius: 0;
}

.outgoing .message-bubble {
  background-color: #dcf8c6;
  border-top-right-radius: 0;
}

.message-text {
  font-size: 14px;
  line-height: 1.4;
  word-wrap: break-word;
}

.message-attachments {
  margin-top: 8px;
}

.attachment {
  margin-bottom: 8px;
}

.attachment-preview {
  max-width: 100%;
  max-height: 200px;
  border-radius: 8px;
  cursor: pointer;
}

.document-attachment {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 8px;
  gap: 8px;
}

.document-attachment i {
  font-size: 20px;
}

.pdf-icon {
  color: #f44336;
}

.word-icon {
  color: #2196f3;
}

.excel-icon {
  color: #4caf50;
}

.ppt-icon {
  color: #ff9800;
}

.archive-icon {
  color: #795548;
}

.text-icon {
  color: #607d8b;
}

.message-meta {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 4px;
  margin-top: 4px;
  font-size: 11px;
  color: rgba(0, 0, 0, 0.45);
}

.message-status .seen {
  color: #4caf50;
}

.typing-indicator {
  padding: 8px 16px;
  background-color: #fff;
  border-top: 1px solid #e0e0e0;
}

.typing-user {
  display: flex;
  align-items: center;
  gap: 8px;
}

.typing-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
}

.typing-dots {
  display: flex;
  gap: 4px;
}

.typing-dots span {
  width: 6px;
  height: 6px;
  background-color: #757575;
  border-radius: 50%;
  animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) {
  animation-delay: 0s;
}

.typing-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-4px); }
}

.chat-input {
  padding: 12px 16px;
  background-color: #fff;
  border-top: 1px solid #e0e0e0;
}

.attachments-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.attachment-item {
  display: flex;
  align-items: center;
  background-color: #f5f5f5;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 13px;
  gap: 8px;
}

.file-name {
  max-width: 120px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.remove-attachment {
  background: none;
  border: none;
  color: #757575;
  cursor: pointer;
  padding: 0;
  margin-left: 4px;
}

.input-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.input-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #616161;
  padding: 8px;
}

.file-input {
  display: none;
}

.message-input {
  flex: 1;
  padding: 10px 16px;
  border: 1px solid #e0e0e0;
  border-radius: 20px;
  font-size: 14px;
  outline: none;
}

.message-input:focus {
  border-color: #3f51b5;
}

.send-button {
  background-color: #3f51b5;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.send-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Scrollbar styles */
.chat-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
}

.chat-messages::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

/* Mobile styles */
@media (max-width: 768px) {
  .conversation-container {
    height: calc(100vh - 56px); /* Adjust for mobile header */
  }
  
  .message-content {
    max-width: 85%;
  }
}
</style>