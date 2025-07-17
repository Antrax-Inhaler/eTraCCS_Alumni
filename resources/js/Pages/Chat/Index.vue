<template>
  <div class="chat-app">
    <!-- Sidebar with conversation list -->
    <div class="chat-sidebar">
      <div class="sidebar-header">
        <h2>Messages</h2>
<button @click="openNewChatModal" class="new-chat-button">
  <i class="fas fa-plus"></i> New Chat
</button>
      </div>
      
      <div class="search-container">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search conversations..." 
          class="search-input"
        >
      </div>
      
      <div class="conversation-list">
        <div 
          v-for="conversation in filteredConversations" 
          :key="conversation.id"
          @click="selectConversation(conversation)"
          :class="['conversation-item', { 'active': activeConversation?.id === conversation.id }]"
        >
          <div class="conversation-avatar">
            <img :src="conversation.avatar" :alt="conversation.name" class="avatar">
            <span v-if="getOnlineStatus(conversation)" class="online-indicator"></span>
          </div>
          
          <div class="conversation-details">
            <div class="conversation-name">{{ conversation.name }}</div>
            <div class="conversation-preview">
              <span v-if="conversation.last_message" class="last-message">
                <span v-if="conversation.last_message.is_current_user">You: </span>
                {{ truncate(conversation.last_message.content, 30) }}
              </span>
            </div>
          </div>
          
          <div class="conversation-meta">
            <span v-if="conversation.last_message" class="last-message-time">
              {{ formatTime(conversation.last_message.created_at) }}
            </span>
            <span v-if="conversation.unread_count > 0" class="unread-count">
              {{ conversation.unread_count }}
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Main chat area -->
    <div class="chat-main">
      <div v-if="activeConversation" class="chat-container">
        <!-- Chat header -->
        <div class="chat-header">
          <div class="header-left">
            <button @click="showSidebarOnMobile" class="back-button mobile-only">
              <i class="fas fa-arrow-left"></i>
            </button>
            
            <div class="user-info">
              <div class="avatar-container">
                <img :src="activeConversation.avatar" :alt="activeConversation.name" class="avatar">
                <span v-if="getOnlineStatus(activeConversation)" class="online-indicator"></span>
              </div>
              
              <div>
                <h3>{{ activeConversation.name }}</h3>
                <p v-if="activeConversation.type === 'personal'" class="user-status">
                  {{ getOnlineStatus(activeConversation) ? 'Online' : 'Offline' }}
                </p>
                <p v-else class="user-status">
                  {{ activeConversation.participants.length }} members
                </p>
              </div>
            </div>
          </div>
          
          <div class="header-right">
            <button v-if="activeConversation.type === 'group'" @click="showGroupInfo = true" class="header-button">
              <i class="fas fa-info-circle"></i>
            </button>
            <button @click="showOptions = !showOptions" class="header-button">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            
            <div v-if="showOptions" class="options-dropdown">
              <button @click="clearConversation" class="dropdown-item">
                <i class="fas fa-trash"></i> Clear chat
              </button>
              <button v-if="activeConversation.type === 'group' && activeConversation.is_admin" 
                      @click="showGroupSettings = true" 
                      class="dropdown-item">
                <i class="fas fa-cog"></i> Group settings
              </button>
              <button v-if="activeConversation.type === 'group'" @click="leaveGroup" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i> Leave group
              </button>
            </div>
          </div>
        </div>
        
        <!-- Chat messages -->
        <div class="chat-messages" ref="messagesContainer">
          <div v-for="message in messages" :key="message.id" 
               :class="['message', message.is_current_user ? 'outgoing' : 'incoming']">
            <div v-if="!message.is_current_user" class="message-avatar">
              <img :src="message.sender_profile_photo_url" :alt="message.sender_name" class="avatar">
            </div>
            
            <div class="message-content">
              <div v-if="!message.is_current_user && activeConversation.type === 'group'" class="sender-name">
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
        
        <!-- Chat input -->
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
            
            <input v-model="newMessage" @keyup.enter="sendMessage" 
                   type="text" placeholder="Type a message..." class="message-input">
            
            <button @click="sendMessage" :disabled="!canSend" class="send-button">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Empty state when no conversation is selected -->
      <div v-else class="empty-state">
        <div class="empty-content">
          <i class="fas fa-comments"></i>
          <h3>Select a conversation to start chatting</h3>
          <p>Or start a new conversation with someone</p>
          <button @click="showNewChatModal = true" class="new-chat-button">
            <i class="fas fa-plus"></i> New Chat
          </button>
        </div>
      </div>
    </div>
    

    <!-- Correct your modal components -->
<NewChatModal 
  v-if="showNewChatModal" 
  @close="showNewChatModal = false"
  @start-chat="startNewChat"
/>


<GroupInfoModal 
  v-if="showGroupInfo && activeConversation" 
  :conversation="activeConversation"
  @close="showGroupInfo = false"
  @add-members="showAddMembersModal = true"
/>

<!-- Make sure all modal components are properly imported -->
    <!-- Modals -->
    <EmojiPicker 
      v-if="showEmojiPicker" 
      @emoji-selected="addEmoji"
      @close="showEmojiPicker = false"
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
    <template>
  <div style="color: red; font-size: 2rem;">
    TEST COMPONENT VISIBILITY
  </div>
</template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import NewChatModal from '@/Components/Chat/NewChatModal.vue';
import GroupInfoModal from '@/Components/Chat/GroupInfoModal.vue';
import GroupSettingsModal from '@/Components/Chat/GroupSettingsModal.vue';
import AddMembersModal from '@/Components/Chat/AddMembersModal.vue';
import AttachmentViewer from '@/Components/Chat/AttachmentViewer.vue';
import EmojiPicker from '@/Components/Chat/EmojiPicker.vue';

// Extend dayjs with plugins
dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);

const props = defineProps({
  conversations: Array,
  current_user: Object,
});

// Refs
const activeConversation = ref(null);
const messages = ref([]);
const newMessage = ref('');
const attachments = ref([]);
const fileInput = ref(null);
const messagesContainer = ref(null);
const searchQuery = ref('');
const lastMessageId = ref(null);
const pollingInterval = ref(null);
const typingPollingInterval = ref(null);
const typingUsers = ref([]);
const typingTimeout = ref(null);
const showNewChatModal = ref(false);
const showGroupInfo = ref(false);
const showGroupSettings = ref(false);
const showAddMembersModal = ref(false);
const selectedAttachment = ref(null);
const showOptions = ref(false);
const showEmojiPicker = ref(false);

// Computed properties
const filteredConversations = computed(() => {
  if (!searchQuery.value) return props.conversations;
  
  const query = searchQuery.value.toLowerCase();
  return props.conversations.filter(conv => {
    return conv.name.toLowerCase().includes(query) || 
           (conv.last_message?.content?.toLowerCase().includes(query) ?? false);
  });
});

const canSend = computed(() => {
  return newMessage.value.trim().length > 0 || attachments.value.length > 0;
});
function openNewChatModal() {
  console.log('Opening new chat modal'); // Debug
  showNewChatModal.value = true;
}

// Methods
function selectConversation(conversation) {
  // Reset messages and state when switching conversations
  messages.value = [];
  lastMessageId.value = null;
  typingUsers.value = [];
  
  // Use the router to navigate to the new chat URL
  router.visit(route('chat.show', { conversation: conversation.id }), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      activeConversation.value = conversation;
      fetchMessages();
      
      // Mark as seen
      if (conversation.unread_count > 0) {
        markAsSeen();
      }
    }
  });
}
async function fetchMessages() {
  if (!activeConversation.value) return;
  
  try {
    const endpoint = activeConversation.value.type === 'personal' 
      ? route('chat.messages', { user: getOtherParticipant().id })
      : route('chat.conversation-messages', { conversation: activeConversation.value.id });
    
    // Reset messages when fetching for a new conversation
    messages.value = [];
    lastMessageId.value = null;
    
    const response = await axios.get(endpoint);
    const newMessages = response.data.messages || [];
    
    if (newMessages.length) {
      messages.value = newMessages; // Replace all messages
      lastMessageId.value = newMessages[newMessages.length - 1].id;
      scrollToBottom();
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
  }
}
function toggleEmojiPicker() {
  showEmojiPicker.value = !showEmojiPicker.value;
}

function addEmoji(emoji) {
  newMessage.value += emoji;
  showEmojiPicker.value = false;
}
async function checkForNewMessages() {
  if (!activeConversation.value || messages.value.length === 0) return;
  
  try {
    const endpoint = activeConversation.value.type === 'personal' 
      ? route('chat.messages', { user: getOtherParticipant().id })
      : route('chat.conversation-messages', { conversation: activeConversation.value.id });
    
    const params = { last_id: lastMessageId.value };
    const response = await axios.get(endpoint, { params });
    
    const newMessages = response.data.messages || [];
    if (newMessages.length) {
      // Filter out any duplicates (shouldn't be necessary but just in case)
      const existingIds = new Set(messages.value.map(m => m.id));
      const filteredMessages = newMessages.filter(msg => !existingIds.has(msg.id));
      
      if (filteredMessages.length > 0) {
        messages.value.push(...filteredMessages);
        lastMessageId.value = filteredMessages[filteredMessages.length - 1].id;
        scrollToBottom();
        
        // Mark as seen if any new messages are from others
        if (filteredMessages.some(msg => !msg.is_current_user)) {
          await markAsSeen();
        }
      }
    }
  } catch (error) {
    console.error('Error checking for new messages:', error);
  }
}
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
async function testChatStart() {
  try {
    const response = await axios.post(route('chat.start', { user: 1 })); // Use a real user ID
    console.log('Response:', response.data);
  } catch (error) {
    console.error('Error testing endpoint:', error.response);
  }
}
async function markAsSeen() {
  if (!activeConversation.value) return;
  
  try {
    const endpoint = activeConversation.value.type === 'personal' 
      ? route('chat.markAsSeen', { user: getOtherParticipant().id })
      : route('chat.conversation.markAsSeen', { conversation: activeConversation.value.id });
    
    await axios.post(endpoint);
  } catch (error) {
    console.error('Error marking messages as seen:', error);
  }
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
async function sendMessage() {
  if (!canSend.value) return;

  const formData = new FormData();
  if (newMessage.value.trim()) {
    formData.append('message', newMessage.value.trim());
  }

  // Add attachments if any
  attachments.value.forEach((file, index) => {
    formData.append(`attachments[${index}]`, file);
  });

  try {
    let endpoint;
    let params = {};

    // Always use conversation endpoint if we have a conversation ID
    if (props.conversation?.id) {
      endpoint = route('chat.conversation.send', { conversation: props.conversation.id });
    } else if (props.conversation?.type === 'personal' && getOtherParticipant.value?.id) {
      // For new personal conversations, use the user endpoint
      endpoint = route('chat.send', { user: getOtherParticipant.value.id });
    } else {
      throw new Error('No valid conversation or participant specified');
    }

    const response = await axios.post(endpoint, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    // If this was a new conversation, update the conversation ID
    if (response.data.conversation_id && !props.conversation?.id) {
      // Update the active conversation in your state management
      // This depends on how you're managing state - here's a basic example:
      activeConversation.value = {
        id: response.data.conversation_id,
        type: 'personal',
        name: getOtherParticipant.value?.name || ''
      };
    }

    // Process the response
    const processedAttachments = response.data.attachments?.map(attachment => ({
      id: attachment.id,
      file_path: attachment.file_path,
      file_type: attachment.file_type
    })) || [];

    // Add the new message to the messages array
    const newMsg = {
      id: response.data.id,
      conversation_id: response.data.conversation_id || props.conversation?.id,
      sender_id: props.currentUser.id,
      is_current_user: true,
      content: response.data.content,
      created_at: response.data.created_at,
      status: response.data.status || 'sent',
      attachments: processedAttachments,
      sender_name: props.currentUser.name,
      sender_profile_photo_url: props.currentUser.profile_photo_url
    };

    messages.value.push(newMsg);
    lastMessageId.value = newMsg.id;

    // Reset input fields
    newMessage.value = '';
    attachments.value = [];
    if (fileInput.value) {
      fileInput.value.value = '';
    }

    scrollToBottom();

  } catch (error) {
    console.error('Error sending message:', error);
    // Show error to user
    alert('Failed to send message: ' + error.message);
  }
}
async function startNewChat(user) {
  console.log('Starting chat with user:', user);
  showNewChatModal.value = false;
  
  try {
    const response = await axios.post(route('chat.start', { user: user.id }));
    console.log('API Response:', response.data);

    const newConversation = {
      id: response.data.conversation_id,
      type: 'personal',
      name: user.name,
      avatar: user.avatar,
      participants: [
        { id: props.current_user.id, name: props.current_user.name },
        { id: user.id, name: user.name }
      ],
      last_message: null,
      unread_count: 0,
      created_at: new Date().toISOString()
    };

    console.log('New conversation:', newConversation);
    selectConversation(newConversation);
    
  } catch (error) {
    console.error('Chat creation failed:', {
      error: error.response?.data || error.message,
      config: error.config
    });
  }
}
function getFileName(filePath) {
    return filePath.split('/').pop();
}

function truncateFileName(fileName, maxLength = 20) {
    if (fileName.length <= maxLength) return fileName;
    return fileName.substring(0, maxLength) + '...';
}

// Function to scroll to bottom
function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}
// async function checkTypingStatus() {
//   if (!activeConversation.value?.id) {
//     console.warn('No active conversation ID available for typing status check');
//     return;
//   }

//   try {
//     const response = await axios.get(route('chat.typing-status', { 
//       conversation: activeConversation.value.id  // Make sure this matches your route parameter name
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
function formatTime(date) {
  return dayjs(date).format('h:mm A');
}
function getOtherParticipant() {
  if (!activeConversation.value || activeConversation.value.type !== 'personal') return null;
  return activeConversation.value.participants.find(p => p.id !== props.current_user.id);
}

function getOnlineStatus(conversation) {
  if (conversation.type === 'personal') {
    const participant = conversation.participants.find(p => p.id !== props.current_user.id);
    return participant?.is_online || false;
  }
  return false;
}
function truncate(text, length) {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
}
// ... (keep all other existing methods the same) ...

async function handleTyping() {
  if (!activeConversation.value || newMessage.value.length === 0) return;
  
  try {
    await axios.post(route('chat.typing'), {
      conversation_id: activeConversation.value.id,
      user_id: props.current_user.id,
      typing: true
    });
  } catch (error) {
    console.error('Error sending typing indicator:', error);
  }
}

// Lifecycle hooks
onMounted(() => {
  console.log('Mounted with props:', {
    conversations: props.conversations,
    current_user: props.current_user
  });
  
  const conversationId = route().params.conversation;
  console.log('Initial conversation ID:', conversationId);

  // Select first conversation if none specified
  if (conversationId) {
    const conversation = props.conversations.find(c => c.id == conversationId);
    if (conversation) selectConversation(conversation);
  } else if (props.conversations.length > 0) {
    // Auto-select first conversation if none specified
    selectConversation(props.conversations[0]);
  }
  
  pollingInterval.value = setInterval(checkForNewMessages, 3000);
});
onBeforeUnmount(() => {
  // Clean up intervals
  clearInterval(pollingInterval.value);
  clearInterval(typingPollingInterval.value);
  
  // Clear any pending typing timeout
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
});

// Watch for active conversation changes
watch(activeConversation, (newVal) => {
  if (newVal) {
    document.title = `${newVal.name} | Chat`;
  } else {
    document.title = 'Chat';
  }
});
</script>

<style scoped>
.chat-app {
  position: relative;
  height: 100vh;
  display: flex;

}

.chat-sidebar {
  width: 350px;
  border-right: 1px solid var(--card-border);
  display: flex;
  flex-direction: column;
  height: 100%;
}

.sidebar-header {
  padding: 16px;
  border-bottom: 1px solid var(--card-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: var(--card-bg);
}

.sidebar-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: var(--text-primary);
}

.new-chat-button {
  background-color: var(--primary);
  color: var(--text-primary);
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s;
}

.new-chat-button:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.search-container {
  padding: 16px;
  border-bottom: 1px solid var(--card-border);
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  background-color: var(--bg-dark);
  border: 1px solid var(--card-border);
  border-radius: 4px;
  font-size: 14px;
  color: var(--text-primary);
}

.search-input::placeholder {
  color: var(--text-secondary);
}

.conversation-list {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  display: flex;
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid var(--card-border);
  transition: background-color 0.2s;
}

.conversation-item:hover {
  background-color: rgba(255, 140, 0, 0.1);
}

.conversation-item.active {
  background-color: var(--primary-light);
}

.conversation-avatar {
  position: relative;
  margin-right: 12px;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.online-indicator {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  background-color: #4caf50;
  border-radius: 50%;
  border: 2px solid var(--bg-darker);
}

.conversation-details {
  flex: 1;
  min-width: 0;
}

.conversation-name {
  font-weight: 500;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--text-primary);
}

.conversation-preview {
  font-size: 13px;
  color: var(--text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.conversation-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-left: 8px;
}

.last-message-time {
  font-size: 12px;
  color: var(--text-secondary);
  white-space: nowrap;
}

.unread-count {
  background-color: var(--primary);
  color: var(--text-primary);
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  margin-top: 4px;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background-image: 
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 20px 20px;
}

.empty-state {
  display: flex;
  flex: 1;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 20px;
}

.empty-content {
  max-width: 400px;
}

.empty-content i {
  font-size: 48px;
  color: var(--text-secondary);
  margin-bottom: 16px;
}

.empty-content h3 {
  margin: 0 0 8px;
  color: var(--text-primary);
}

.empty-content p {
  margin: 0 0 16px;
  color: var(--text-secondary);
}

.chat-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.chat-header {
  padding: 12px 16px;
  background-color: var(--card-bg);
  border-bottom: 1px solid var(--card-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  backdrop-filter: blur(12px);
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
  color: var(--text-secondary);
  display: none;
  transition: all 0.3s;
}

.back-button:hover {
  color: var(--primary);
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
  color: var(--text-primary);
}

.user-status {
  margin: 0;
  font-size: 13px;
  color: var(--text-secondary);
}

.header-right {
  position: relative;
}

.header-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: var(--text-secondary);
  margin-left: 12px;
  transition: all 0.3s;
}

.header-button:hover {
  color: var(--primary);
}

.options-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  background-color: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  z-index: 10;
  min-width: 180px;
  backdrop-filter: blur(12px);
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
  color: var(--text-primary);
}

.dropdown-item:hover {
  background-color: var(--primary-light);
}

.chat-messages {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
    background-image: 
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-image: none;
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

.message-avatar {
  margin-right: 8px;
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
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.message-bubble {
  padding: 8px 12px;
  border-radius: 18px;
  position: relative;
}

.incoming .message-bubble {
  background-color: var(--card-bg);
  border-top-left-radius: 0;
}

.outgoing .message-bubble {
  background-color: var(--primary-light);
  border-top-right-radius: 0;
}

.message-text {
  font-size: 14px;
  line-height: 1.4;
  word-wrap: break-word;
  color: var(--text-primary);
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
  background-color: var(--card-bg);
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
  color: var(--text-secondary);
}

.message-status .seen {
  color: #4caf50;
}

.chat-input {
  padding: 12px 16px;
  background-color: var(--card-bg);
  border-top: 1px solid var(--card-border);
  backdrop-filter: blur(12px);
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
  background-color: var(--bg-dark);
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 13px;
  gap: 8px;
  color: var(--text-primary);
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
  color: var(--text-secondary);
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
  color: var(--text-secondary);
  padding: 8px;
  transition: all 0.3s;
}

.input-button:hover {
  color: var(--primary);
}

.file-input {
  display: none;
}

.message-input {
  flex: 1;
  padding: 10px 16px;
  background-color: var(--bg-dark);
  border: 1px solid var(--card-border);
  border-radius: 20px;
  font-size: 14px;
  outline: none;
  color: var(--text-primary);
}

.message-input:focus {
  border-color: var(--primary);
}

.send-button {
  background-color: var(--primary);
  color: var(--text-primary);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.send-button:hover:not(:disabled) {
  transform: scale(1.05);
}

.send-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Mobile styles */
@media (max-width: 768px) {
  .chat-sidebar {
    width: 100%;
    display: none;
  }
  
  .chat-sidebar.show {
    display: flex;
  }
  
  .back-button {
    display: block;
  }
  
  .message-content {
    max-width: 85%;
  }
}

/* Scrollbar styles */
.chat-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.chat-messages::-webkit-scrollbar-thumb {
  background: var(--primary);
  border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 140, 0, 0.8);
}

/* Card styles for conversation items */
.conversation-item {
  background: var(--card-bg);
  margin: 8px;
  border-radius: 8px;
  border: 1px solid var(--card-border);
  backdrop-filter: blur(12px);
  transition: all 0.3s;
}

.conversation-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>