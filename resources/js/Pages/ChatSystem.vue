<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useEncrypt } from '@/Composables/useEncrypt';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';

// Extend dayjs with plugins
dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);

const props = defineProps({
    initialConversations: {
        type: Array,
        default: () => []
    },
    user: {
        type: Object,
        required: true
    }
});

const { encrypt, decrypt } = useEncrypt();

// State
const conversations = ref(props.initialConversations);
const selectedConversation = ref(null);
const messages = ref([]);
const newMessage = ref('');
const attachments = ref([]);
const loadingMessages = ref(false);
const hasMoreMessages = ref(true);
const page = ref(1);
const showCreateGroupModal = ref(false);
const showAddParticipantModal = ref(false);
const newGroupName = ref('');
const selectedParticipants = ref([]);
const availableUsers = ref([]);
const educationalBackgrounds = ref([]);

// Computed properties
const paginatedConversations = computed(() => conversations.value);
const hasMoreConversations = computed(() => conversations.value.length < totalConversations.value);

// Methods
const formatMessageTimestamp = (timestamp) => {
    return dayjs(timestamp).format('LT'); // 3:45 PM
};

const formatConversationDate = (timestamp) => {
    return dayjs(timestamp).calendar(null, {
        sameDay: '[Today]',
        lastDay: '[Yesterday]',
        lastWeek: 'dddd',
        sameElse: 'MMM D, YYYY'
    });
};

const isCurrentUser = (senderId) => {
    return senderId === props.user.id;
};

const getConversationName = (conversation) => {
    if (conversation.type !== 'personal') {
        return conversation.name || `Group ${conversation.id}`;
    }
    
    const otherParticipant = conversation.participants.find(
        p => p.user_id !== props.user.id
    );
    
    return otherParticipant?.user?.name || 'Unknown User';
};

const getUnreadCount = (conversation) => {
    return conversation.unread_count || 0;
};

const selectConversation = async (conversation) => {
    selectedConversation.value = conversation;
    messages.value = [];
    page.value = 1;
    hasMoreMessages.value = true;
    
    await fetchMessages();
    markMessagesAsSeen();
};

const fetchMessages = async () => {
    if (!selectedConversation.value || loadingMessages.value) return;
    
    loadingMessages.value = true;
    
    try {
        const response = await axios.get(`/api/messages/${selectedConversation.value.encrypted_id}`, {
            params: {
                page: page.value
            }
        });
        
        if (response.data.messages.length === 0) {
            hasMoreMessages.value = false;
        } else {
            messages.value = [...response.data.messages.reverse(), ...messages.value];
            educationalBackgrounds.value = response.data.educational_backgrounds;
        }
    } catch (error) {
        console.error('Error fetching messages:', error);
    } finally {
        loadingMessages.value = false;
    }
};

const loadMoreMessages = async () => {
    if (!hasMoreMessages.value) return;
    
    page.value += 1;
    await fetchMessages();
};

const markMessagesAsSeen = async () => {
    if (!selectedConversation.value) return;
    
    try {
        await axios.post(`/api/conversations/${selectedConversation.value.encrypted_id}/mark-as-seen`);
    } catch (error) {
        console.error('Error marking messages as seen:', error);
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() && attachments.value.length === 0) return;
    
    const formData = new FormData();
    formData.append('content', newMessage.value);
    formData.append('_token', usePage().props.csrf_token);
    
    attachments.value.forEach(file => {
        formData.append('attachments[]', file);
    });
    
    try {
        const response = await axios.post(
            `/api/messages/${selectedConversation.value.encrypted_id}`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );
        
        messages.value.push(response.data.message);
        newMessage.value = '';
        attachments.value = [];
        
        // Scroll to bottom
        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

const handleFileUpload = (event) => {
    attachments.value = Array.from(event.target.files);
};

const createDirectMessage = async (userId) => {
    try {
        const response = await axios.post('/api/conversations/direct', {
            user_id: userId
        });
        
        // Add to conversations if not already present
        if (!conversations.value.some(c => c.id === response.data.conversation.id)) {
            conversations.value.unshift(response.data.conversation);
        }
        
        selectConversation(response.data.conversation);
    } catch (error) {
        console.error('Error creating direct message:', error);
    }
};

const createGroupChat = async () => {
    try {
        const formData = new FormData();
        formData.append('name', newGroupName.value);
        formData.append('participants', JSON.stringify(selectedParticipants.value.map(p => p.id)));
        formData.append('_token', usePage().props.csrf_token);
        
        const response = await axios.post('/api/conversations/group', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        conversations.value.unshift(response.data.conversation);
        selectConversation(response.data.conversation);
        showCreateGroupModal.value = false;
        newGroupName.value = '';
        selectedParticipants.value = [];
    } catch (error) {
        console.error('Error creating group chat:', error);
    }
};

const fetchAvailableUsers = async () => {
    try {
        const response = await axios.get('/api/users/available');
        availableUsers.value = response.data.users;
    } catch (error) {
        console.error('Error fetching available users:', error);
    }
};

const addParticipant = (user) => {
    if (!selectedParticipants.value.some(p => p.id === user.id)) {
        selectedParticipants.value.push(user);
    }
};

const removeParticipant = (userId) => {
    selectedParticipants.value = selectedParticipants.value.filter(p => p.id !== userId);
};

const isSelected = (userId) => {
    return selectedParticipants.value.some(p => p.id === userId);
};

const isAlreadyParticipant = (userId) => {
    return selectedConversation.value?.participants.some(p => p.user_id === userId);
};

const addParticipantToGroup = async (user) => {
    try {
        await axios.post(`/api/conversations/${selectedConversation.value.encrypted_id}/participants`, {
            user_id: user.id
        });
        
        // Refresh conversation data
        const response = await axios.get(`/api/conversations/${selectedConversation.value.encrypted_id}`);
        selectedConversation.value = response.data.conversation;
    } catch (error) {
        console.error('Error adding participant:', error);
    }
};

const removeParticipantFromGroup = async (userId) => {
    try {
        await axios.delete(`/api/conversations/${selectedConversation.value.encrypted_id}/participants/${userId}`);
        
        // Refresh conversation data
        const response = await axios.get(`/api/conversations/${selectedConversation.value.encrypted_id}`);
        selectedConversation.value = response.data.conversation;
        
        // If current user was removed, go back to conversation list
        if (userId === props.user.id) {
            selectedConversation.value = null;
        }
    } catch (error) {
        console.error('Error removing participant:', error);
    }
};

const canRemoveParticipant = (userId) => {
    return props.user.id === selectedConversation.value.created_by || userId === props.user.id;
};

const scrollToBottom = () => {
    const container = document.querySelector('.messages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
};

const scrollToMessage = (messageId) => {
    const messageElement = document.querySelector(`.message[data-message-id="${messageId}"]`);
    if (messageElement) {
        messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        messageElement.classList.add('highlight');
        setTimeout(() => {
            messageElement.classList.remove('highlight');
        }, 2000);
    }
};

const getMessage = (messageId) => {
    return messages.value.find(m => m.id === messageId);
};

const isImage = (fileType) => {
    return fileType === 'image';
};

const isVideo = (fileType) => {
    return fileType === 'video';
};

// Long polling for new messages
const longPoll = async () => {
    if (!selectedConversation.value) return;
    
    const lastMessageId = messages.value.length > 0 
        ? messages.value[messages.value.length - 1].id 
        : null;
    
    try {
        const response = await axios.get(`/api/messages/${selectedConversation.value.encrypted_id}/poll`, {
            params: { last_message_id: lastMessageId }
        });
        
        if (response.data.messages.length > 0) {
            messages.value = [...messages.value, ...response.data.messages];
            scrollToBottom();
        }
    } catch (error) {
        console.error('Long poll error:', error);
    } finally {
        // Continue polling
        setTimeout(longPoll, 1000);
    }
};

// Initialize
onMounted(() => {
    if (conversations.value.length > 0) {
        selectConversation(conversations.value[0]);
    }
    
    // Start long polling when conversation is selected
    watch(selectedConversation, (newVal) => {
        if (newVal) {
            longPoll();
        }
    });
    
    // Fetch available users when modal is shown
    watch(showCreateGroupModal, (newVal) => {
        if (newVal) {
            fetchAvailableUsers();
        }
    });
    
    watch(showAddParticipantModal, (newVal) => {
        if (newVal) {
            fetchAvailableUsers();
        }
    });
});
</script>

<template>
  <div class="chat-container">
    <!-- Conversation List -->
    <div class="conversation-list">
      <h3>Conversations</h3>
      <ul>
        <li
          v-for="conversation in paginatedConversations"
          :key="conversation.id"
          :class="{ active: selectedConversation?.id === conversation.id }"
          @click="selectConversation(conversation)"
        >
          <!-- Profile Picture -->
          <div class="avatar-container">
            <img
              v-if="conversation.profile_picture"
              :src="conversation.profile_picture"
              alt="Profile Picture"
              class="conversation-avatar"
            />
            <span v-else class="default-avatar">
              {{ getConversationName(conversation).charAt(0).toUpperCase() }}
            </span>
          </div>
          
          <div class="conversation-data">
            <strong>{{ getConversationName(conversation) }}</strong>
            
            <!-- Latest Message -->
            <div class="latest-message">
              <template v-if="conversation.latest_message">
                <span class="sender-name">
                  {{ conversation.latest_message.sender_id === user.id ? 'You' : conversation.latest_message.sender_name }}:
                </span>
                <span class="message-content">
                  {{ conversation.latest_message.content || 'Attachment' }}
                </span>
                <span class="timestamp">
                  {{ formatMessageTimestamp(conversation.latest_message.created_at) }}
                </span>
              </template>
              <template v-else>
                <span class="placeholder-message">Start a conversation now!</span>
              </template>
            </div>
          </div>
          
          <!-- Unread Indicator -->
          <span v-if="getUnreadCount(conversation)" class="unread-indicator">
            {{ getUnreadCount(conversation) }}
          </span>
        </li>
      </ul>
      
      <button @click="loadMoreConversations" v-if="hasMoreConversations">
        Load More
      </button>
      
      <!-- Create Group Chat Button -->
      <button @click="showCreateGroupModal = true" class="create-group-btn">
        Create Group Chat
      </button>
      
      <!-- Create Group Modal -->
      <div v-if="showCreateGroupModal" class="modal-overlay">
        <div class="modal">
          <h3>Create Group Chat</h3>
          <form @submit.prevent="createGroupChat">
            <label for="group-name">Group Name:</label>
            <input type="text" id="group-name" v-model="newGroupName" required />
            
            <label>Participants:</label>
            <div class="user-list">
              <div v-for="user in availableUsers" :key="user.id" class="user-item">
                <span>{{ user.name }}</span>
                <button
                  v-if="!isSelected(user.id)"
                  @click="addParticipant(user)"
                  class="add-participant-btn"
                >
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>
            
            <div class="selected-participants">
              <h4>Selected Participants:</h4>
              <div v-if="selectedParticipants.length > 0">
                <span
                  v-for="participant in selectedParticipants"
                  :key="participant.id"
                  class="participant-tag"
                >
                  {{ participant.name }}
                  <button @click="removeParticipant(participant.id)">
                    <i class="fas fa-times"></i>
                  </button>
                </span>
              </div>
              <p v-else>No participants selected.</p>
            </div>
            
            <div class="modal-actions">
              <button type="button" @click="showCreateGroupModal = false">Cancel</button>
              <button type="submit">Create</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Message View -->
    <div class="message-view" v-if="selectedConversation">
      <!-- Conversation Header -->
      <div class="conversation-header">
        <div class="avatar-container">
          <img
            v-if="selectedConversation.profile_picture"
            :src="selectedConversation.profile_picture"
            alt="Profile Picture"
            class="conversation-avatar"
          />
          <span v-else class="default-avatar">
            {{ getConversationName(selectedConversation).charAt(0).toUpperCase() }}
          </span>
        </div>
        
        <h3>{{ getConversationName(selectedConversation) }}</h3>
        
        <!-- Educational Background Info (for personal chats) -->
        <div v-if="selectedConversation.type === 'personal'" class="education-info">
          <div v-for="bg in educationalBackgrounds" :key="bg.user_id">
            <template v-if="bg.user_id !== user.id">
              <p v-if="bg.college">
                <strong>Education:</strong> {{ bg.college }} 
                <span v-if="bg.year_graduated">({{ bg.year_graduated }})</span>
              </p>
              <p v-if="bg.currently_studying">
                Currently studying
                <span v-if="bg.is_graduate_study">(Graduate studies)</span>
              </p>
              <p v-if="bg.reason_for_study">
                Reason: {{ bg.reason_for_study }}
              </p>
            </template>
          </div>
        </div>
      </div>
      
      <!-- Messages -->
      <div class="messages" ref="messagesContainer" @scroll="handleScroll">
        <div
          v-for="message in messages"
          :key="message.id"
          :class="['message', { 'message-right': isCurrentUser(message.sender_id) }]"
          :data-message-id="message.id"
        >
          <!-- Sender Avatar -->
          <div v-if="!isCurrentUser(message.sender_id)" class="sender-avatar">
            <img
              v-if="message.sender.profile_picture"
              :src="message.sender.profile_picture"
              alt="Sender Profile Picture"
              class="sender-avatar-image"
            />
            <span v-else class="default-avatar">
              {{ message.sender.name.charAt(0).toUpperCase() }}
            </span>
          </div>
          
          <div class="message-content-wrapper">
            <!-- Sender Name -->
            <strong v-if="!isCurrentUser(message.sender_id)">
              {{ message.sender.name }}
            </strong>
            
            <!-- Message Content -->
            <p v-if="message.content">{{ message.content }}</p>
            
            <!-- Attachments -->
            <div v-if="message.attachments && message.attachments.length > 0" class="attachments">
              <div v-for="attachment in message.attachments" :key="attachment.id">
                <img
                  v-if="isImage(attachment.file_type)"
                  :src="`/storage/${attachment.file_path}`"
                  alt="Attachment"
                  class="attachment-image"
                />
                <video
                  v-else-if="isVideo(attachment.file_type)"
                  controls
                  class="attachment-video"
                >
                  <source :src="`/storage/${attachment.file_path}`" :type="`video/${attachment.file_type.split('/')[1]}`" />
                </video>
                <a
                  v-else
                  :href="`/storage/${attachment.file_path}`"
                  target="_blank"
                  class="attachment-file"
                >
                  Download File
                </a>
              </div>
            </div>
            
            <!-- Reply Button -->
            <button
              v-if="message.reply_to_message_id"
              @click="scrollToMessage(message.reply_to_message_id)"
              class="reply-indicator"
            >
              Replying to: {{ getMessage(message.reply_to_message_id)?.content || 'Message' }}
            </button>
            
            <!-- Time and Status -->
            <div class="time-status">
              <span class="message-timestamp">
                {{ formatMessageTimestamp(message.created_at) }}
              </span>
              
              <span v-if="isCurrentUser(message.sender_id)" class="message-status">
                <i
                  v-if="message.status === 'sent'"
                  class="fas fa-check"
                  title="Sent"
                ></i>
                <i
                  v-else-if="message.status === 'delivered'"
                  class="fas fa-check-double"
                  title="Delivered"
                ></i>
                <i
                  v-else-if="message.status === 'seen'"
                  class="fas fa-check-double text-primary"
                  title="Seen"
                ></i>
              </span>
            </div>
          </div>
        </div>
        
        <div v-if="loadingMessages" class="loading-indicator">
          Loading more messages...
        </div>
      </div>
      
      <!-- Message Input -->
      <div class="message-input">
        <textarea
          v-model="newMessage"
          placeholder="Type a message..."
          @keydown.enter.exact.prevent="sendMessage"
        ></textarea>
        
        <div class="input-actions">
          <label class="file-upload-label">
            <input
              type="file"
              @change="handleFileUpload"
              accept="image/*, video/*, .pdf, .doc, .docx"
              multiple
              class="file-upload-input"
            />
            <i class="fas fa-paperclip"></i>
          </label>
          
          <button @click="sendMessage" class="send-button">
            <i class="fas fa-paper-plane"></i>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Group Details (for group chats) -->
    <div v-if="selectedConversation?.type === 'group'" class="group-details">
      <h4>Group Details</h4>
      <p><strong>Creator:</strong> {{ selectedConversation.creator?.name }}</p>
      
      <h5>Participants:</h5>
      <ul>
        <li v-for="participant in selectedConversation.participants" :key="participant.user_id">
          {{ participant.user.name }}
          <button
            v-if="canRemoveParticipant(participant.user_id)"
            @click="removeParticipantFromGroup(participant.user_id)"
            class="remove-participant-btn"
          >
            <i class="fas fa-times"></i>
          </button>
        </li>
      </ul>
      
      <button @click="showAddParticipantModal = true" class="add-participant-btn">
        Add Participant
      </button>
    </div>
    
    <!-- Add Participant Modal -->
    <div v-if="showAddParticipantModal" class="modal-overlay">
      <div class="modal">
        <h3>Add Participant</h3>
        <div class="user-list">
          <div v-for="user in availableUsers" :key="user.id" class="user-item">
            <span>{{ user.name }}</span>
            <button
              v-if="!isAlreadyParticipant(user.id)"
              @click="addParticipantToGroup(user)"
              class="add-participant-btn"
            >
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" @click="showAddParticipantModal = false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add your styles here */
.chat-container {
  display: flex;
  height: 100vh;
}

.conversation-list {
  width: 300px;
  border-right: 1px solid #ddd;
  overflow-y: auto;
}

.message-view {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.message-input {
  padding: 1rem;
  border-top: 1px solid #ddd;
}

/* Add more styles as needed */
</style>