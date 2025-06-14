<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

// Current user
const user = computed(() => usePage().props.auth.user);

// State
const conversations = ref([]);
const selectedConversation = ref(null);
const messages = ref([]);
const newMessage = ref('');
const loadingMessages = ref(false);
const hasMoreMessages = ref(true);
const lastMessageId = ref(null);
const showCreateGroupModal = ref(false);
const showAddParticipantModal = ref(false);
const newGroupName = ref('');
const newGroupProfilePicture = ref(null);
const selectedParticipants = ref([]);
const availableUsers = ref([]);
const searchQuery = ref('');
const attachments = ref([]);
const replyToMessage = ref(null);
const pagination = ref({
    current_page: 1,
    last_page: 1
});

// Long polling for new messages
const longPoll = async () => {
    if (!selectedConversation.value) return;
    
    try {
        const response = await axios.get('/api/chat/long-poll', {
            params: {
                conversation_id: selectedConversation.value.encrypted_id,
                last_message_id: lastMessageId.value,
                timeout: 25000 // 25 seconds
            },
            timeout: 30000 // Axios timeout
        });
        
        if (response.data.messages.length > 0) {
            // Add new messages to the beginning of the array
            messages.value = [...response.data.messages, ...messages.value];
            lastMessageId.value = response.data.messages[response.data.messages.length - 1].id;
            
            // Play sound for new messages not from current user
            if (response.data.messages.some(msg => msg.sender_id !== user.value.id)) {
                playNotificationSound();
            }
        }
        
        // Immediately start next long poll
        longPoll();
    } catch (error) {
        console.error('Long poll error:', error);
        // Retry after a delay
        setTimeout(longPoll, 3000);
    }
};

// Fetch conversations
const fetchConversations = async (page = 1, loadMore = false) => {
    try {
        const response = await axios.get('/api/chat/conversations', {
            params: {
                page,
                search: searchQuery.value
            }
        });
        
        if (loadMore) {
            conversations.value = [...conversations.value, ...response.data.data];
        } else {
            conversations.value = response.data.data;
        }
        
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page
        };
    } catch (error) {
        console.error('Error fetching conversations:', error);
    }
};

// Select conversation
const selectConversation = async (conversation) => {
    selectedConversation.value = conversation;
    messages.value = [];
    lastMessageId.value = null;
    hasMoreMessages.value = true;
    
    await fetchMessages();
    
    // Start long polling
    longPoll();
};

// Fetch messages
const fetchMessages = async (loadMore = false) => {
    if (!selectedConversation.value || loadingMessages.value) return;
    
    loadingMessages.value = true;
    
    try {
        const response = await axios.get(`/api/chat/messages/${selectedConversation.value.encrypted_id}`, {
            params: {
                before: loadMore ? messages.value[0]?.id : null
            }
        });
        
        if (response.data.messages.data.length > 0) {
            if (loadMore) {
                messages.value = [...response.data.messages.data, ...messages.value];
            } else {
                messages.value = response.data.messages.data;
                lastMessageId.value = messages.value[messages.value.length - 1]?.id;
                
                // Scroll to bottom
                nextTick(() => {
                    scrollToBottom();
                });
            }
        }
        
        if (response.data.messages.current_page === response.data.messages.last_page) {
            hasMoreMessages.value = false;
        }
    } catch (error) {
        console.error('Error fetching messages:', error);
    } finally {
        loadingMessages.value = false;
    }
};

// Send message
const sendMessage = async () => {
    if (!newMessage.value.trim() && attachments.value.length === 0) return;
    
    const formData = new FormData();
    formData.append('content', newMessage.value);
    
    if (replyToMessage.value) {
        formData.append('reply_to', replyToMessage.value.id);
    }
    
    attachments.value.forEach(file => {
        formData.append('attachments[]', file);
    });
    
    try {
        const response = await axios.post(
            `/api/chat/send/${selectedConversation.value.encrypted_id}`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );
        
        // Add the new message to the list
        messages.value = [...messages.value, response.data.message];
        newMessage.value = '';
        attachments.value = [];
        replyToMessage.value = null;
        
        // Scroll to bottom
        nextTick(() => {
            scrollToBottom();
        });
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

// Create direct conversation
const createDirectConversation = async (userId) => {
    try {
        const response = await axios.post('/api/chat/direct', { user_id: userId });
        
        // Add to conversations if not already there
        if (!conversations.value.some(c => c.id === response.data.conversation.id)) {
            conversations.value = [response.data.conversation, ...conversations.value];
        }
        
        selectConversation(response.data.conversation);
    } catch (error) {
        console.error('Error creating direct conversation:', error);
    }
};

// Create group conversation
const createGroupChat = async () => {
    if (!newGroupName.value.trim() || selectedParticipants.value.length === 0) return;
    
    const participantIds = selectedParticipants.value.map(p => p.id);
    
    try {
        const formData = new FormData();
        formData.append('name', newGroupName.value);
        formData.append('participants[]', participantIds);
        
        if (newGroupProfilePicture.value) {
            formData.append('profile_picture', newGroupProfilePicture.value);
        }
        
        const response = await axios.post('/api/chat/group', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        // Add to conversations
        conversations.value = [response.data.conversation, ...conversations.value];
        selectConversation(response.data.conversation);
        
        // Reset form
        showCreateGroupModal.value = false;
        newGroupName.value = '';
        newGroupProfilePicture.value = null;
        selectedParticipants.value = [];
    } catch (error) {
        console.error('Error creating group chat:', error);
    }
};

// Search users
const searchUsers = async () => {
    try {
        const response = await axios.get('/api/chat/search-users', {
            params: {
                search: searchQuery.value
            }
        });
        
        availableUsers.value = response.data;
    } catch (error) {
        console.error('Error searching users:', error);
    }
};

// Add participant to group
const addParticipantToGroup = async (user) => {
    try {
        await axios.post(`/api/chat/${selectedConversation.value.encrypted_id}/participants`, {
            user_id: user.id
        });
        
        // Refresh conversation data
        const response = await axios.get(`/api/chat/conversation/${selectedConversation.value.encrypted_id}`);
        selectedConversation.value = response.data;
        
        showAddParticipantModal.value = false;
    } catch (error) {
        console.error('Error adding participant:', error);
    }
};

// Handle file upload
const handleFileUpload = (event) => {
    attachments.value = Array.from(event.target.files);
};

// Handle profile picture upload
const handleProfilePictureUpload = (event) => {
    newGroupProfilePicture.value = event.target.files[0];
};

// Helper functions
const isCurrentUser = (senderId) => senderId === user.value.id;

const formatMessageTimestamp = (timestamp) => {
    return dayjs(timestamp).fromNow();
};

const getConversationName = (conversation) => {
    if (conversation.type !== 'personal') return conversation.name;
    
    const otherParticipant = conversation.participants.find(
        p => p.user_id !== user.value.id
    );
    
    return otherParticipant?.user?.name || 'Unknown';
};

const getUnreadCount = (conversation) => {
    return conversation.unread_messages_count || 0;
};

const playNotificationSound = () => {
    const audio = new Audio('/notification.mp3');
    audio.play().catch(e => console.error('Error playing sound:', e));
};

const scrollToBottom = () => {
    const container = document.querySelector('.messages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
};

const scrollToMessage = (messageId) => {
    const element = document.querySelector(`[data-message-id="${messageId}"]`);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        element.classList.add('highlight');
        setTimeout(() => element.classList.remove('highlight'), 2000);
    }
};

const isImage = (fileType) => fileType === 'image';
const isVideo = (fileType) => fileType === 'video';

const canRemoveParticipant = (participantId) => {
    return selectedConversation.value?.created_by === user.value.id && 
           participantId !== user.value.id;
};

const isAlreadyParticipant = (userId) => {
    return selectedConversation.value?.participants?.some(p => p.user_id === userId);
};

const isSelected = (userId) => {
    return selectedParticipants.value.some(p => p.id === userId);
};

const addParticipant = (user) => {
    if (!isSelected(user.id)) {
        selectedParticipants.value = [...selectedParticipants.value, user];
    }
};

const removeParticipant = (userId) => {
    selectedParticipants.value = selectedParticipants.value.filter(p => p.id !== userId);
};

const removeParticipantFromGroup = async (userId) => {
    try {
        await axios.delete(`/api/chat/${selectedConversation.value.encrypted_id}/participants/${userId}`);
        
        // Refresh conversation data
        const response = await axios.get(`/api/chat/conversation/${selectedConversation.value.encrypted_id}`);
        selectedConversation.value = response.data;
    } catch (error) {
        console.error('Error removing participant:', error);
    }
};

// Load more conversations
const loadMoreConversations = () => {
    if (pagination.value.current_page < pagination.value.last_page) {
        fetchConversations(pagination.value.current_page + 1, true);
    }
};

// Load more messages when scrolling up
const handleScroll = (event) => {
    const container = event.target;
    if (container.scrollTop === 0 && hasMoreMessages.value && !loadingMessages.value) {
        fetchMessages(true);
    }
};

// Initialize
onMounted(() => {
    fetchConversations();
    searchUsers();
});

// Watch for search query changes
watch(searchQuery, debounce(() => {
    searchUsers();
}, 300));

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}
</script>

<template>
  <div class="chat-container">
    <!-- Conversation List -->
    <div class="conversation-list">
      <div class="conversation-header">
        <h3>Messages</h3>
        <div class="search-bar">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search conversations..." 
            @input="fetchConversations(1, false)"
          />
          <i class="fas fa-search"></i>
        </div>
      </div>
      
      <ul>
        <li
          v-for="conversation in conversations"
          :key="conversation.id"
          :class="{ active: selectedConversation?.id === conversation.id }"
          @click="selectConversation(conversation)"
        >
          <div class="conversation-item">
            <div class="avatar-container">
              <img
                v-if="conversation.profile_picture"
                :src="`/storage/${conversation.profile_picture}`"
                alt="Profile Picture"
                class="conversation-avatar"
              />
              <span v-else class="default-avatar">
                {{ getConversationName(conversation).charAt(0).toUpperCase() }}
              </span>
            </div>
            
            <div class="conversation-details">
              <div class="conversation-name">
                {{ getConversationName(conversation) }}
                <span v-if="conversation.type === 'group'" class="group-badge">Group</span>
              </div>
              
              <div class="latest-message">
                <template v-if="conversation.latest_message">
                  <span class="sender-name" v-if="conversation.type === 'group' && !isCurrentUser(conversation.latest_message.sender_id)">
                    {{ conversation.latest_message.sender.name }}:
                  </span>
                  <span class="message-content">
                    {{ conversation.latest_message.content?.substring(0, 30) }}
                    {{ conversation.latest_message.content?.length > 30 ? '...' : '' }}
                  </span>
                </template>
                <template v-else>
                  <span class="placeholder-message">No messages yet</span>
                </template>
              </div>
            </div>
            
            <div class="conversation-meta">
              <span class="timestamp" v-if="conversation.latest_message">
                {{ formatMessageTimestamp(conversation.latest_message.created_at) }}
              </span>
              <span v-if="getUnreadCount(conversation)" class="unread-indicator">
                {{ getUnreadCount(conversation) }}
              </span>
            </div>
          </div>
        </li>
      </ul>
      
      <button 
        @click="loadMoreConversations" 
        v-if="pagination.current_page < pagination.last_page" 
        class="load-more-btn"
      >
        Load More Conversations
      </button>
      
      <button @click="showCreateGroupModal = true" class="create-group-btn">
        <i class="fas fa-plus"></i> Create Group
      </button>
    </div>
    
    <!-- Main Chat Area -->
    <div class="chat-area" v-if="selectedConversation">
      <div class="chat-header">
        <div class="header-left">
          <div class="avatar-container">
            <img
              v-if="selectedConversation.profile_picture"
              :src="`/storage/${selectedConversation.profile_picture}`"
              alt="Profile Picture"
              class="conversation-avatar"
            />
            <span v-else class="default-avatar">
              {{ getConversationName(selectedConversation).charAt(0).toUpperCase() }}
            </span>
          </div>
          
          <div class="header-info">
            <h3>{{ getConversationName(selectedConversation) }}</h3>
            <div class="participant-info" v-if="selectedConversation.type === 'personal'">
              <span v-for="participant in selectedConversation.participants.filter(p => p.user_id !== user.id)" :key="participant.user_id">
                <!-- Display educational background -->
                <template v-if="participant.user.educational_backgrounds?.length > 0">
                  <span class="education-info">
                    {{ participant.user.educational_backgrounds[0].college }}
                    ({{ participant.user.educational_backgrounds[0].year_graduated }})
                    <span v-if="participant.user.educational_backgrounds[0].is_graduate_study" class="graduate-badge">
                      Graduate
                    </span>
                  </span>
                </template>
              </span>
            </div>
            <div class="group-info" v-else-if="selectedConversation.type === 'group'">
              <span>{{ selectedConversation.participants.length }} members</span>
            </div>
          </div>
        </div>
        
        <div class="header-actions" v-if="selectedConversation.type === 'group'">
          <button @click="showAddParticipantModal = true" class="add-participant-btn">
            <i class="fas fa-user-plus"></i> Add Participant
          </button>
        </div>
      </div>
      
      <div class="messages" @scroll="handleScroll" ref="messagesContainer">
        <div v-if="loadingMessages" class="loading-indicator">
          <i class="fas fa-spinner fa-spin"></i> Loading messages...
        </div>
        
        <div 
          v-for="message in messages" 
          :key="message.id" 
          :data-message-id="message.id"
          :class="['message', { 'current-user': isCurrentUser(message.sender_id) }]"
        >
          <div class="message-content-wrapper">
            <!-- Sender info for group chats -->
            <div class="sender-info" v-if="selectedConversation.type === 'group' && !isCurrentUser(message.sender_id)">
              <img
                v-if="message.sender.profile_photo_path"
                :src="`/storage/${message.sender.profile_photo_path}`"
                alt="Sender Profile Picture"
                class="sender-avatar"
              />
              <span v-else class="default-avatar small">
                {{ message.sender.name.charAt(0).toUpperCase() }}
              </span>
              <span class="sender-name">{{ message.sender.name }}</span>
            </div>
            
            <!-- Reply indicator -->
            <div class="reply-indicator" v-if="message.reply_to_message_id">
              <i class="fas fa-reply"></i>
              Replying to: {{ message.reply_to?.content?.substring(0, 30) }}...
            </div>
            
            <!-- Message content -->
            <div class="message-bubble">
              <p>{{ message.content }}</p>
              
              <!-- Attachments -->
              <div v-if="message.attachments?.length > 0" class="attachments">
                <div v-for="attachment in message.attachments" :key="attachment.id" class="attachment">
                  <img
                    v-if="isImage(attachment.file_type)"
                    :src="`/storage/${attachment.file_path}`"
                    alt="Attachment"
                    @click="openLightbox(`/storage/${attachment.file_path}`)"
                  />
                  <video
                    v-else-if="isVideo(attachment.file_type)"
                    controls
                  >
                    <source :src="`/storage/${attachment.file_path}`" :type="`video/${attachment.file_path.split('.').pop()}`" />
                  </video>
                  <a
                    v-else
                    :href="`/storage/${attachment.file_path}`"
                    target="_blank"
                    class="file-download"
                  >
                    <i class="fas fa-file-download"></i> Download File
                  </a>
                </div>
              </div>
              
              <!-- Message status and timestamp -->
              <div class="message-footer">
                <span class="timestamp">{{ formatMessageTimestamp(message.created_at) }}</span>
                <span v-if="isCurrentUser(message.sender_id)" class="status">
                  <i v-if="message.status === 'sent'" class="fas fa-check"></i>
                  <i v-else-if="message.status === 'delivered'" class="fas fa-check-double"></i>
                  <i v-else-if="message.status === 'seen'" class="fas fa-check-double seen"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Message input area -->
      <div class="message-input">
        <div class="input-actions">
          <button @click="openFilePicker" class="attachment-btn" title="Attach file">
            <i class="fas fa-paperclip"></i>
            <input 
              type="file" 
              ref="fileInput" 
              @change="handleFileUpload" 
              accept="image/*, video/*, .pdf, .doc, .docx, .xls, .xlsx" 
              multiple 
              hidden
            />
          </button>
          
          <button 
            v-if="replyToMessage" 
            @click="replyToMessage = null" 
            class="cancel-reply-btn"
            title="Cancel reply"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="reply-preview" v-if="replyToMessage">
          Replying to: {{ replyToMessage.content.substring(0, 50) }}...
        </div>
        
        <textarea
          v-model="newMessage"
          placeholder="Type a message..."
          @keydown.enter.exact.prevent="sendMessage"
          @keydown.enter.shift.exact.prevent="newMessage += '\n'"
          ref="messageInput"
        ></textarea>
        
        <button @click="sendMessage" class="send-btn" :disabled="!newMessage.trim() && attachments.length === 0">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
      
      <!-- Attachment preview -->
      <div v-if="attachments.length > 0" class="attachment-preview">
        <div v-for="(file, index) in attachments" :key="index" class="attachment-item">
          <i class="fas fa-file"></i>
          <span>{{ file.name }}</span>
          <button @click="removeAttachment(index)" class="remove-attachment">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Empty state -->
    <div v-else class="empty-state">
      <div class="empty-content">
        <i class="fas fa-comments"></i>
        <h3>Select a conversation to start chatting</h3>
        <p>Or start a new conversation by searching for a user</p>
      </div>
    </div>
    
    <!-- Create Group Modal -->
    <div v-if="showCreateGroupModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>Create Group Chat</h3>
          <button @click="showCreateGroupModal = false" class="close-modal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label for="group-name">Group Name</label>
            <input 
              type="text" 
              id="group-name" 
              v-model="newGroupName" 
              placeholder="Enter group name" 
              required
            />
          </div>
          
          <div class="form-group">
            <label for="group-photo">Group Photo (optional)</label>
            <input 
              type="file" 
              id="group-photo" 
              @change="handleProfilePictureUpload" 
              accept="image/*"
            />
          </div>
          
          <div class="form-group">
            <label>Add Participants</label>
            <input 
              type="text" 
              v-model="searchQuery" 
              @input="searchUsers" 
              placeholder="Search users..."
            />
            
            <div class="user-list">
              <div 
                v-for="user in availableUsers" 
                :key="user.id" 
                class="user-item"
                @click="addParticipant(user)"
              >
                <div class="user-avatar">
                  <img
                    v-if="user.profile_photo_path"
                    :src="`/storage/${user.profile_photo_path}`"
                    alt="User Profile Picture"
                  />
                  <span v-else class="default-avatar small">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                
                <div class="user-info">
                  <span class="user-name">{{ user.name }}</span>
                  <span class="user-education" v-if="user.educational_backgrounds?.length > 0">
                    {{ user.educational_backgrounds[0].college }} ({{ user.educational_backgrounds[0].year_graduated }})
                  </span>
                </div>
                
                <div class="user-action">
                  <i 
                    :class="['fas', isSelected(user.id) ? 'fa-check-circle' : 'fa-plus-circle']"
                    :style="{ color: isSelected(user.id) ? '#4CAF50' : '#999' }"
                  ></i>
                </div>
              </div>
            </div>
          </div>
          
          <div class="selected-participants">
            <h4>Selected Participants ({{ selectedParticipants.length }})</h4>
            <div v-if="selectedParticipants.length > 0" class="participant-tags">
              <div v-for="participant in selectedParticipants" :key="participant.id" class="tag">
                {{ participant.name }}
                <button @click="removeParticipant(participant.id)" class="remove-tag">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
            <p v-else class="no-participants">No participants selected yet</p>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="showCreateGroupModal = false" class="btn-cancel">Cancel</button>
          <button 
            @click="createGroupChat" 
            class="btn-create"
            :disabled="!newGroupName.trim() || selectedParticipants.length === 0"
          >
            Create Group
          </button>
        </div>
      </div>
    </div>
    
    <!-- Add Participant Modal -->
    <div v-if="showAddParticipantModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>Add Participant</h3>
          <button @click="showAddParticipantModal = false" class="close-modal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <input 
              type="text" 
              v-model="searchQuery" 
              @input="searchUsers" 
              placeholder="Search users..."
            />
            
            <div class="user-list">
              <div 
                v-for="user in availableUsers" 
                :key="user.id" 
                class="user-item"
                @click="addParticipantToGroup(user)"
                v-if="!isAlreadyParticipant(user.id)"
              >
                <div class="user-avatar">
                  <img
                    v-if="user.profile_photo_path"
                    :src="`/storage/${user.profile_photo_path}`"
                    alt="User Profile Picture"
                  />
                  <span v-else class="default-avatar small">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                
                <div class="user-info">
                  <span class="user-name">{{ user.name }}</span>
                  <span class="user-education" v-if="user.educational_backgrounds?.length > 0">
                    {{ user.educational_backgrounds[0].college }} ({{ user.educational_backgrounds[0].year_graduated }})
                  </span>
                </div>
                
                <div class="user-action">
                  <i class="fas fa-plus-circle"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="showAddParticipantModal = false" class="btn-cancel">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chat-container {
  display: flex;
  height: 100vh;
  background-color: #f5f5f5;
}

.conversation-list {
  width: 350px;
  border-right: 1px solid #e0e0e0;
  background-color: white;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.conversation-header {
  padding: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.search-bar {
  position: relative;
  margin-top: 10px;
}

.search-bar input {
  width: 100%;
  padding: 8px 30px 8px 10px;
  border: 1px solid #ddd;
  border-radius: 20px;
  outline: none;
}

.search-bar i {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.conversation-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
  flex-grow: 1;
}

.conversation-list li {
  padding: 12px 15px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.2s;
}

.conversation-list li:hover {
  background-color: #f9f9f9;
}

.conversation-list li.active {
  background-color: #e3f2fd;
}

.conversation-item {
  display: flex;
  align-items: center;
}

.avatar-container {
  position: relative;
  margin-right: 12px;
}

.conversation-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

.default-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: #2196F3;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 18px;
}

.default-avatar.small {
  width: 35px;
  height: 35px;
  font-size: 14px;
}

.conversation-details {
  flex-grow: 1;
  min-width: 0;
}

.conversation-name {
  font-weight: 500;
  margin-bottom: 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.group-badge {
  font-size: 11px;
  background-color: #e3f2fd;
  color: #1976D2;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 5px;
}

.latest-message {
  font-size: 13px;
  color: #666;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sender-name {
  font-weight: 500;
  margin-right: 3px;
}

.conversation-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-left: 10px;
}

.timestamp {
  font-size: 11px;
  color: #999;
  white-space: nowrap;
}

.unread-indicator {
  background-color: #2196F3;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  margin-top: 3px;
}

.load-more-btn {
  padding: 10px;
  background-color: #f5f5f5;
  border: none;
  width: 100%;
  cursor: pointer;
  color: #666;
}

.load-more-btn:hover {
  background-color: #eee;
}

.create-group-btn {
  margin: 15px;
  padding: 10px 15px;
  background-color: #2196F3;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.create-group-btn i {
  margin-right: 5px;
}

.create-group-btn:hover {
  background-color: #1976D2;
}

.chat-area {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  background-color: #f0f2f5;
}

.empty-state {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
}

.empty-content {
  text-align: center;
  color: #666;
}

.empty-content i {
  font-size: 60px;
  color: #ddd;
  margin-bottom: 20px;
}

.empty-content h3 {
  margin-bottom: 10px;
}

.chat-header {
  padding: 15px;
  background-color: white;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
}

.header-info {
  margin-left: 12px;
}

.header-info h3 {
  margin: 0;
  font-size: 16px;
}

.participant-info, .group-info {
  font-size: 13px;
  color: #666;
  margin-top: 3px;
}

.education-info {
  display: inline-flex;
  align-items: center;
}

.graduate-badge {
  font-size: 11px;
  background-color: #4CAF50;
  color: white;
  padding: 1px 5px;
  border-radius: 10px;
  margin-left: 5px;
}

.header-actions button {
  background: none;
  border: none;
  color: #2196F3;
  cursor: pointer;
  font-size: 13px;
}

.header-actions button:hover {
  text-decoration: underline;
}

.messages {
  flex-grow: 1;
  padding: 20px;
  overflow-y: auto;
  background-color: #e5ddd5;
  background-image: url('https://web.whatsapp.com/img/bg-chat-tile-light_a4be512e7195b6b733d9110b408f075d.png');
  background-repeat: repeat;
}

.loading-indicator {
  text-align: center;
  padding: 10px;
  color: #666;
  font-size: 14px;
}

.message {
  margin-bottom: 15px;
  display: flex;
}

.message.current-user {
  justify-content: flex-end;
}

.message-content-wrapper {
  max-width: 70%;
}

.sender-info {
  display: flex;
  align-items: center;
  margin-bottom: 3px;
}

.sender-avatar {
  width: 25px;
  height: 25px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 8px;
}

.sender-name {
  font-size: 12px;
  font-weight: 500;
  color: #333;
}

.reply-indicator {
  font-size: 12px;
  color: #666;
  margin-bottom: 3px;
  display: flex;
  align-items: center;
}

.reply-indicator i {
  margin-right: 5px;
}

.message-bubble {
  padding: 8px 12px;
  border-radius: 7.5px;
  position: relative;
  word-wrap: break-word;
}

.message:not(.current-user) .message-bubble {
  background-color: white;
}

.message.current-user .message-bubble {
  background-color: #dcf8c6;
}

.attachments {
  margin-top: 8px;
}

.attachment {
  margin-top: 5px;
}

.attachment img {
  max-width: 100%;
  max-height: 300px;
  border-radius: 5px;
  cursor: pointer;
}

.attachment video {
  max-width: 100%;
  max-height: 300px;
  border-radius: 5px;
}

.file-download {
  display: inline-flex;
  align-items: center;
  color: #2196F3;
  text-decoration: none;
}

.file-download i {
  margin-right: 5px;
}

.message-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 3px;
}

.timestamp {
  font-size: 11px;
  color: #666;
  margin-right: 5px;
}

.status {
  font-size: 11px;
}

.status .seen {
  color: #4CAF50;
}

.message-input {
  padding: 10px;
  background-color: white;
  display: flex;
  align-items: center;
  position: relative;
}

.input-actions {
  display: flex;
  margin-right: 10px;
}

.attachment-btn, .cancel-reply-btn {
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  font-size: 18px;
  padding: 5px;
}

.attachment-btn:hover, .cancel-reply-btn:hover {
  color: #2196F3;
}

.reply-preview {
  position: absolute;
  top: -30px;
  left: 0;
  right: 0;
  background-color: #f5f5f5;
  padding: 5px 10px;
  font-size: 13px;
  border-top: 1px solid #e0e0e0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.message-input textarea {
  flex-grow: 1;
  border: none;
  border-radius: 20px;
  padding: 10px 15px;
  resize: none;
  outline: none;
  max-height: 100px;
  background-color: #f0f2f5;
}

.send-btn {
  background: none;
  border: none;
  color: #2196F3;
  cursor: pointer;
  font-size: 18px;
  margin-left: 10px;
  padding: 5px 10px;
}

.send-btn:disabled {
  color: #ccc;
  cursor: not-allowed;
}

.attachment-preview {
  padding: 5px 15px;
  background-color: white;
  border-top: 1px solid #e0e0e0;
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.attachment-item {
  background-color: #f5f5f5;
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 12px;
  display: flex;
  align-items: center;
}

.attachment-item i {
  margin-right: 5px;
  color: #666;
}

.remove-attachment {
  background: none;
  border: none;
  color: #999;
  cursor: pointer;
  margin-left: 5px;
  font-size: 10px;
}

.remove-attachment:hover {
  color: #f44336;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background-color: white;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 15px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
}

.close-modal {
  background: none;
  border: none;
  font-size: 18px;
  color: #666;
  cursor: pointer;
}

.close-modal:hover {
  color: #333;
}

.modal-body {
  padding: 15px;
  overflow-y: auto;
  flex-grow: 1;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="file"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.user-list {
  max-height: 200px;
  overflow-y: auto;
  margin-top: 10px;
  border: 1px solid #eee;
  border-radius: 4px;
}

.user-item {
  padding: 10px;
  display: flex;
  align-items: center;
  cursor: pointer;
  border-bottom: 1px solid #f5f5f5;
}

.user-item:hover {
  background-color: #f9f9f9;
}

.user-avatar {
  margin-right: 10px;
}

.user-avatar img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-info {
  flex-grow: 1;
}

.user-name {
  font-weight: 500;
  display: block;
}

.user-education {
  font-size: 12px;
  color: #666;
  display: block;
}

.user-action {
  color: #666;
}

.selected-participants {
  margin-top: 15px;
}

.selected-participants h4 {
  margin: 0 0 10px 0;
  font-size: 14px;
}

.participant-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.tag {
  background-color: #e3f2fd;
  color: #1976D2;
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 13px;
  display: flex;
  align-items: center;
}

.remove-tag {
  background: none;
  border: none;
  color: #1976D2;
  margin-left: 5px;
  cursor: pointer;
  font-size: 10px;
}

.no-participants {
  font-size: 13px;
  color: #666;
  margin: 0;
}

.modal-footer {
  padding: 15px;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn-cancel {
  padding: 8px 15px;
  background-color: #f5f5f5;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-cancel:hover {
  background-color: #eee;
}

.btn-create {
  padding: 8px 15px;
  background-color: #2196F3;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-create:hover {
  background-color: #1976D2;
}

.btn-create:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.highlight {
  animation: highlight 2s;
}

@keyframes highlight {
  0% { background-color: rgba(255, 255, 0, 0.5); }
  100% { background-color: transparent; }
}
</style>