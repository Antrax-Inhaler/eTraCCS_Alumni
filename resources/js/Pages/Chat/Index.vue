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
            :src="`${conversation.profile_picture}`"
            alt="Profile Picture"
            class="conversation-avatar"
        />
        <span v-else class="default-avatar">{{ getConversationName(conversation).charAt(0).toUpperCase() }}</span>
    </div>
    <div class="conversation-data">
        <strong>{{ getConversationName(conversation) }}</strong>

<!-- Latest Message -->
<div class="latest-message">
    <template v-if="conversation.latest_message">
        <span class="sender-name">{{ conversation.latest_message.sender_name }}:</span>
        <span class="message-content">{{ conversation.latest_message.content }}</span>
        <span class="timestamp">{{ formatMessageTimestamp(conversation.latest_message.created_at) }}</span>
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
<button @click="loadMoreConversations" v-if="hasMoreConversations">Load More</button>

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

            <!-- Profile Picture -->
            <label for="profile-picture">Profile Picture:</label>
            <input type="file" id="profile-picture" @change="handleProfilePictureUpload" accept="image/*" />

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

    <div class="message-view" v-if="selectedConversation">
    <!-- Profile Picture -->
    <div class="conversation-header">
        <div class="avatar-container">
            <img
                v-if="selectedConversation.profile_photo_path"
                :src="`/storage/${selectedConversation.profile_photo_path}`"
                alt="Profile Picture"
                class="conversation-avatar"
            />
            <span v-else class="default-avatar">{{ getConversationName(selectedConversation).charAt(0).toUpperCase() }}</span>
        </div>

        <!-- Conversation Name -->
        <h3>{{ getConversationName(selectedConversation) }}</h3>
    </div>

    <!-- Messages -->
    <div class="messages" ref="messagesContainer" @scroll="handleScroll">
        <div
            v-for="message in messages"
            :key="message.id"
            :class="['message', { 'message-right': isCurrentUser(message.sender_id) }]"
        >
            <!-- Sender Avatar (only for non-current users) -->
            <div v-if="!isCurrentUser(message.sender_id)" class="sender-avatar">
                <img
                    v-if="message.sender_profile_picture"
                    :src="message.sender_profile_picture"
                    alt="Sender Profile Picture"
                    class="sender-avatar-image"
                />
                <span v-else class="default-avatar">{{ message.sender_name.charAt(0).toUpperCase() }}</span>
            </div>
<div>
      <!-- Sender Name -->
      <strong v-if="!isCurrentUser(message.sender_id)">
                {{ message.sender_name }}
            </strong>

            <!-- Message Content -->
            <p>{{ message.content }}</p>

            <!-- Render Attachments -->
            <div v-if="message.attachments && message.attachments.length > 0">
                <div v-for="(attachment, index) in message.attachments" :key="index" class="post-container">
                    <!-- Render Image -->
                    <img
                        v-if="isImage(attachment.file_type)"
                        class="post-image"
                        :src="`/storage/${attachment.file_path}`"
                        alt="Attachment"
                    />

                    <!-- Render Video -->
                    <video
                        v-else-if="isVideo(attachment.file_type)"
                        controls
                        style="max-width: 300px;"
                    >
                        <source :src="`/storage/${attachment.file_path}`" :type="attachment.file_type" />
                        Your browser does not support the video tag.
                    </video>

                    <!-- Render Other File Types -->
                    <div v-else>
                        <a :href="`/storage/${attachment.file_path}`" target="_blank">Download File</a>
                    </div>
                </div>
            </div>
            <!-- Reply Button -->
            <button v-if="message.reply_to_message_id" @click="scrollToMessage(message.reply_to_message_id)">
                Reply to: {{ getMessage(message.reply_to_message_id)?.content }}
            </button>

            <div class="time-status">
 <!-- Timestamp -->
 <span class="message-timestamp">
                {{ formatMessageTimestamp(message.created_at) }}
            </span>



            <!-- Message Status -->
            <span v-if="isCurrentUser(message.sender_id)" class="message-status">
                <i
                    v-if="message.status === 'sent'"
                    class="fas fa-check"
                    title="Sent"
                ></i>
                <i
                    v-else-if="message.status === 'delivered'"
                    class="fas fa-check-double text-secondary"
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
        <div v-if="loadingMessages" class="loading-indicator">Loading more messages...</div>
    </div>

    <!-- Message Input -->
    <div class="message-input">
        <textarea
            v-model="newMessage"
            placeholder="Type a message..."
            @keydown.enter.prevent="sendMessage"
        ></textarea>
        <input class="upload-input" type="file" @change="handleFileUpload" accept="image/*, video/*" multiple />
        <button @click="sendMessage">Send</button>
    </div>
</div>
<div v-if="selectedConversation?.type === 'group'" class="group-details">
    <h4>Group Details</h4>
    <p><strong>Creator:</strong> {{ selectedConversation.creator.name }}</p>
    <h5>Participants:</h5>
    <ul>
        <li v-for="participant in selectedConversation.participants" :key="participant.id">
            {{ participant.user.name }}
            <button
                v-if="canRemoveParticipant(participant.user.id)"
                @click="removeParticipantFromGroup(participant.user.id)"
                class="remove-participant-btn"
            >
                <i class="fas fa-times"></i>
            </button>
        </li>
    </ul>
    <button @click="showAddParticipantModal = true">Add Participant</button>
</div>

<!-- Modal for Adding Participants -->
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
  
<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';

// Extend dayjs with plugins
dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);
// Reactive State
const selectedConversation = ref(null);
const newMessage = ref('');
const files = ref([]);
const hasMoreConversations = ref(true);
const loadingMessages = ref(false);
const showCreateGroupModal = ref(false);
const newGroupName = ref('');
const selectedParticipants = ref([]); // Array of selected participants
const availableUsers = ref([]);
const conversations = ref([]);
const currentPage = ref(1);
const perPage = 10;
const user = ref(null);
const messages = ref([]);
const messagePagination = ref({});
const users = ref([]);
const showAddParticipantModal = ref(false);
const profilePicture = ref(null);
const lastMessageId = ref(null);
const pollingInterval = ref(null);
const isPollingActive = ref(false);


const paginatedConversations = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    const end = start + perPage;
    return conversations.value.slice(start, end);
});

const loadMoreConversations = () => {
    currentPage.value += 1;
    fetchConversations();
};

const getConversationName = (conversation) => {
    if (conversation.type === 'global') {
        return 'Global Chat';
    } else if (conversation.type === 'batch') {
        return `Batch ${conversation.year_graduated}`;
    } else if (conversation.type === 'campus') {
        return `${conversation.campus} Campus`;
    } else if (conversation.type === 'personal') {
        const otherParticipant = conversation.participants.find(
            (p) => p.user_id !== user.value.id
        );
        return otherParticipant ? otherParticipant.name : 'Private Chat';
    } else if (conversation.type === 'group') {
        return conversation.name || 'Group Chat';
    }
    return 'Unknown Chat';
};
// Fetch Available Users for Group Creation
const fetchAvailableUsers = async () => {
  try {
    const response = await axios.get('/api/users'); // Assuming you have an API endpoint for fetching users
    availableUsers.value = response.data.map((user) => ({ ...user, isSelected: false }));
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};
const fetchConversations = async () => {
    try {
        const response = await axios.get('/api/chat', {
            params: { page: currentPage.value },
        });

        // Append new conversations to the existing list
        conversations.value = [...conversations.value, ...response.data.data];

        // Update pagination metadata
        hasMoreConversations.value = response.data.pagination.has_more;
    } catch (error) {
        console.error('Error fetching conversations:', error.response?.data || error.message);
    }
};

const fetchUserData = async () => {
    try {
        const response = await axios.get('/api/user'); // Adjust the endpoint if needed
        user.value = response.data;
    } catch (error) {
        console.error('Error fetching user data:', error);
        user.value = {}; // Fallback to an empty object
    }
};

// Add Participant
const addParticipant = (user) => {
  selectedParticipants.value.push(user);
  user.isSelected = true; // Mark the user as selected
};

// Remove Participant
const removeParticipant = (userId) => {
  selectedParticipants.value = selectedParticipants.value.filter(
    (participant) => participant.id !== userId
  );
  const user = availableUsers.value.find((u) => u.id === userId);
  if (user) user.isSelected = false; // Unmark the user as selected
};

// Check if a User is Already Selected
const isSelected = (userId) => {
  return selectedParticipants.value.some((participant) => participant.id === userId);
};
const getUnreadCount = (conversation) => {
    if (!conversation.messages) return 0;
    return conversation.messages.filter((msg) => !msg.is_read).length;
};
// Create Group Chat
const createGroupChat = async () => {
    try {
        const formData = new FormData();
        formData.append('name', newGroupName.value);
        formData.append('participants', JSON.stringify(selectedParticipants.value.map((p) => p.id)));

        // Append the profile picture if one is selected
        if (profilePicture.value) {
            formData.append('profile_picture', profilePicture.value);
        }

        const response = await axios.post('/api/chat/group', formData, {
            headers: {
                'Content-Type': 'multipart/form-data', // Required for file uploads
            },
        });

        // Add the new group chat to the conversation list
        conversations.value.unshift(response.data);

        // Close the modal
        showCreateGroupModal.value = false;
        newGroupName.value = '';
        selectedParticipants.value = [];
        profilePicture.value = null; // Reset the profile picture state
    } catch (error) {
        console.error('Error creating group chat:', error.response?.data || error.message);
    }
};
// const selectConversation = async (conversation) => {
//     selectedConversation.value = conversation;

//     // Reset pagination for messages
//     messagePagination.value = {};
//     await fetchMessages(conversation.id);
// };
// Handle Infinite Scroll for Messages
// const handleScroll = () => {
//     const container = document.querySelector('.messages');
//     if (!container) return;

//     const { scrollTop, scrollHeight, clientHeight } = container;
//     if (scrollTop + clientHeight >= scrollHeight - 50 && messagePagination.value.has_more) {
//         messagePagination.value.current_page += 1;
//         fetchMessages(selectedConversation.value.id, true);
//     }
// };

// Fetch Messages for a Conversation
// const fetchMessages = async (conversationId, append = false) => {
//     try {
//         const response = await axios.get(`/api/conversations/${conversationId}/messages`, {
//             params: { page: messagePagination.value.current_page || 1 },
//         });

//         if (append) {
//             messages.value = [...response.data.data, ...messages.value]; // Prepend new messages
//         } else {
//             messages.value = response.data.data;
//         }

//         // Update pagination metadata
//         messagePagination.value = response.data.pagination;
//     } catch (error) {
//         console.error('Error fetching messages:', error.response?.data || error.message);
//     }
// };
const fetchMessages = async (conversationId, append = false) => {
    try {
        const response = await axios.get(`/api/conversations/${conversationId}/messages`);
        
        messages.value = response.data.data;
        lastMessageId.value = messages.value.length > 0 
            ? messages.value[messages.value.length - 1].id 
            : null;
            
        messagePagination.value = response.data.pagination;
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
};

const handleFileUpload = (event) => {
    files.value = Array.from(event.target.files);
};
const sendMessage = async () => {
    if (!newMessage.value.trim() && files.value.length === 0) return;

    const formData = new FormData();
    formData.append('conversation_id', selectedConversation.value.id);
    formData.append('content', newMessage.value);

    // Append files to the form data
    files.value.forEach((file) => {
        formData.append('attachments[]', file);
    });

    try {
        const response = await axios.post('/api/messages', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        // Add the new message to the messages array
        messages.value.push(response.data);

        // Clear input fields
        newMessage.value = '';
        files.value = [];
        document.querySelector('input[type="file"]').value = ''; // Reset file input

        // Scroll to the bottom of the messages container
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error.response?.data || error.message);
    }
};

// Scroll to Bottom of Messages
const scrollToBottom = () => {
    const messagesContainer = document.querySelector('.messages');
    if (messagesContainer) {
        messagesContainer.scrollTop = 0; // Scroll to top in reverse mode
    }
};

// Call this function whenever new messages are added
watch(messages, () => {
    scrollToBottom();
}, { deep: true });
const getUser = (userId) => {
    return users.value.find((user) => user.id === userId) || { name: 'Unknown User' };
};

// Fetch All Users
const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/users'); // Adjust the endpoint if needed
        users.value = response.data;
    } catch (error) {
        console.error('Error fetching users:', error);
    }
};
// Check if a user is already a participant
const isAlreadyParticipant = (userId) => {
    return selectedConversation.value.participants.some(
        (participant) => participant.user.id === userId
    );
};
// Helper Function to Format Message Timestamp
const formatMessageTimestamp = (timestamp) => {
    const now = dayjs();
    const messageTime = dayjs(timestamp);

    // If the message is older than 7 days, show the full date
    if (now.diff(messageTime, 'days') > 7) {
        return messageTime.format('lll'); // e.g., "Oct 1, 2023 12:34 PM"
    }

    // Otherwise, show relative time (e.g., "5 minutes ago")
    return messageTime.fromNow();
};
// Add Participant to Group
const addParticipantToGroup = async (user) => {
    try {
        await axios.post(`/api/conversations/${selectedConversation.value.id}/add-participant`, {
            user_id: user.id,
        });

        // Update the participants list
        selectedConversation.value.participants.push({ user });

        // Close the modal
        showAddParticipantModal.value = false;
    } catch (error) {
        console.error('Error adding participant:', error.response?.data || error.message);
    }
};

// Remove Participant from Group
const removeParticipantFromGroup = async (userId) => {
    try {
        await axios.post(`/api/conversations/${selectedConversation.value.id}/remove-participant`, {
            user_id: userId,
        });

        // Update the participants list
        selectedConversation.value.participants = selectedConversation.value.participants.filter(
            (participant) => participant.user.id !== userId
        );
    } catch (error) {
        console.error('Error removing participant:', error.response?.data || error.message);
    }
};

const canRemoveParticipant = (userId) => {
    return (
        userId === user.value.id || // Allow self-removal
        selectedConversation.value.created_by === user.value.id // Allow creator to remove others
    );
};
const selectConversation = async (conversation) => {
  stopPolling();

    selectedConversation.value = conversation;

    // Save the selected conversation ID to localStorage
    localStorage.setItem('selectedConversationId', conversation.id);

    // Reset pagination for messages
    messagePagination.value = {};
    await fetchMessages(conversation.id);

    // Mark messages as delivered if they are not already
    const undeliveredMessageIds = messages.value
        .filter((msg) => msg.status === 'sent' && msg.sender_id !== user.value.id)
        .map((msg) => msg.id);

    if (undeliveredMessageIds.length > 0) {
        await markMessagesAsDelivered(conversation.id, undeliveredMessageIds);
    }

    // Fetch group details if it's a group chat
    if (conversation.type === 'group') {
        try {
            const response = await axios.get(`/api/conversations/${conversation.id}`);
            selectedConversation.value = response.data;
        } catch (error) {
            console.error('Error fetching group details:', error);
        }
    }
    startPolling();

};

const stopPolling = () => {
    clearInterval(pollingInterval.value);
    isPollingActive.value = false;
};

const fetchNewMessages = async () => {
    if (!selectedConversation.value) return;
    
    try {
        const response = await axios.get('/api/chat/new-messages', {
            params: {
                conversation_id: selectedConversation.value.id,
                last_message_id: lastMessageId.value
            }
        });
        
        if (response.data.messages.length > 0) {
            // Add new messages to the beginning of the array (for reverse chronological order)
            messages.value = [...response.data.messages.reverse(), ...messages.value];
            lastMessageId.value = response.data.last_message_id;
            
            // Optional: Play sound for new messages
            if (messages.value.some(msg => msg.sender_id !== user.value.id)) {
                playNotificationSound();
            }
        }
    } catch (error) {
        console.error('Error fetching new messages:', error);
    }
};

const markMessagesAsDelivered = async (conversationId, messageIds) => {
    try {
        await axios.post(`/api/conversations/${conversationId}/mark-delivered`, {
            message_ids: messageIds,
        });

        // Update the status locally
        messages.value = messages.value.map((msg) =>
            messageIds.includes(msg.id) ? { ...msg, status: 'delivered' } : msg
        );
    } catch (error) {
        console.error('Error marking messages as delivered:', error.response?.data || error.message);
    }
};
const markMessagesAsSeen = async (conversationId, messageIds) => {
    try {
        await axios.post(`/api/conversations/${conversationId}/mark-seen`, {
            message_ids: messageIds,
        });

        // Update the status locally
        messages.value = messages.value.map((msg) =>
            messageIds.includes(msg.id) ? { ...msg, status: 'seen' } : msg
        );
    } catch (error) {
        console.error('Error marking messages as seen:', error);
    }
};
// Helper function to check if a file is an image
const isImage = (fileType) => {
    return fileType && fileType.startsWith('image/');
};

// Helper function to check if a file is a video
const isVideo = (fileType) => {
    return fileType && fileType.startsWith('video/');
};
const handleScroll = async () => {
    const container = document.querySelector('.messages');
    if (!container) return;

    const { scrollTop, scrollHeight, clientHeight } = container;

    // Check if the user has scrolled to the top of the container
    if (scrollTop === 0 && messagePagination.value.has_more) {
        try {
            loadingMessages.value = true;

            // Increment the page number
            messagePagination.value.current_page += 1;

            // Fetch older messages
            const response = await axios.get(`/api/conversations/${selectedConversation.value.id}/messages`, {
                params: { page: messagePagination.value.current_page },
            });

            // Prepend older messages to the top of the list
            messages.value = [...response.data.data, ...messages.value];

            // Update pagination metadata
            messagePagination.value = response.data.pagination;
        } catch (error) {
            console.error('Error fetching older messages:', error.response?.data || error.message);
        } finally {
            loadingMessages.value = false;
        }
    }
};
const logout = async () => {
    try {
        // Send a request to the backend to log out
        await axios.post('/logout'); // Adjust the endpoint if needed (e.g., /api/logout)

        // Clear local storage
        localStorage.removeItem('selectedConversationId');
        localStorage.removeItem('auth_token'); // Remove the authentication token if stored

        // Reset reactive states
        selectedConversation.value = null;
        conversations.value = [];
        messages.value = [];
        user.value = null;

        // Redirect to login page
        window.location.href = '/login'; // Replace with your login route
    } catch (error) {
        console.error('Error during logout:', error.response?.data || error.message);
        alert('An error occurred while logging out. Please try again.');
    }
};
const longPoll = async () => {
    if (!selectedConversation.value) return;
    
    try {
        const response = await axios.get('/api/chat/long-poll', {
            params: {
                conversation_id: selectedConversation.value.id,
                last_message_id: lastMessageId.value,
                timeout: 25000 // 25 seconds
            },
            timeout: 30000 // Axios timeout slightly longer than server timeout
        });
        
        if (response.data.messages.length > 0) {
            messages.value = [...response.data.messages.reverse(), ...messages.value];
            lastMessageId.value = response.data.last_message_id;
            
            // Optional: Play sound for new messages
            if (messages.value.some(msg => msg.sender_id !== user.value.id)) {
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

// Update startPolling to use longPoll instead
const startPolling = () => {
    if (!selectedConversation.value || isPollingActive.value) return;
    isPollingActive.value = true;
    longPoll();
};
// Helper Function to Check if a Message Belongs to the Current User
const isCurrentUser = (senderId) => {
    return senderId === user.value?.id;
};
// // Lifecycle Hook
// onMounted(() => {
//   fetchConversations();
//   fetchAvailableUsers(); // Fetch users for group creation
//   fetchUserData();
//   fetchUsers();
  
// });
const handleProfilePictureUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        profilePicture.value = file; // Store the file in the state
    }
};
onMounted(async () => {
    await   fetchUserData();
    await fetchAvailableUsers();
    await fetchUsers();
    await fetchConversations();
    // Retrieve the saved conversation ID from localStorage
    // const savedConversationId = localStorage.getItem('selectedConversationId');
    // if (savedConversationId) {
    //     const savedConversation = conversations.value.find(
    //         (conversation) => conversation.id === parseInt(savedConversationId)
    //     );

    //     if (savedConversation) {
    //         await selectConversation(savedConversation);
    //     }
    // }
    const savedConversationId = localStorage.getItem('selectedConversationId');
    if (savedConversationId) {
        const conversation = conversations.value.find(c => c.id == savedConversationId);
        if (conversation) {
            selectConversation(conversation);
        }
    }
    
    // Start polling for conversation updates
    const conversationPolling = setInterval(() => {
        fetchConversations();
    }, 10000); // Check for new conversations every 10 seconds
    
    // Cleanup on unmount
    onBeforeUnmount(() => {
        stopPolling();
        clearInterval(conversationPolling);
    });
});
// Watch for Selected Conversation Changes
watch(selectedConversation, () => {
  if (selectedConversation.value) {
    fetchMessages(selectedConversation.value.id);
  }
});
</script>
  
  <style scoped>
/* Base Styles */
.chat-container {
  display: flex;
  height: 100vh;
  font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
  background-color: #1a1a1a;
  color: #e0e0e0;
}

/* Conversation List */
.conversation-list {
  width: 300px;
  background-color: #252525;
  border-right: 1px solid #333;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.conversation-list h3 {
  padding: 15px;
  margin: 0;
  background-color: #333;
  color: #fff;
  font-size: 1.2rem;
  border-bottom: 1px solid #444;
}

.conversation-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
  flex-grow: 1;
}

.conversation-list li {
  padding: 12px 15px;
  border-bottom: 1px solid #333;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
  display: flex;
}

.conversation-list li:hover {
  background-color: #333;
}

.conversation-list li.active {
  background-color: #3a3a3a;
  border-left: 3px solid #4CAF50;
}

.latest-message {
  font-size: 0.85rem;
  color: #aaa;
  margin-top: 5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}

.sender-name {
  color: #4CAF50;
  margin-right: 5px;
}

.timestamp {
  float: right;
  font-size: 0.75rem;
  color: #777;
}

.unread-indicator {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #4CAF50;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
}

/* Message View */
.message-view {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  background-color: #1e1e1e;
}

.conversation-header  {
  padding: 5px;
  margin: 0;
  background-color: #333;
  color: #fff;
  font-size: 1.2rem;
  border-bottom: 1px solid #444;
  display: flex;
  gap: 10px;
  align-items: center;
}

.messages {
  flex-grow: 1;
  padding: 15px;
  overflow-y: auto;
  background-color: #1e1e1e;
  background-image: 
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 20px 20px;
  max-width: 1400px;

}

.message {
  margin-bottom: 15px;
  padding: 10px 15px;
  background-color: #333;
  border-radius: 10px;
  max-width: 70%;
  width: fit-content;
  position: relative;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  word-wrap: break-word;
  max-width: 70%;
  display: flex;

}

.message-right {
  margin-left: auto;
  background-color: #4CAF50;
  color: white;
  
}
.messages {
    display: flex;
    flex-direction: column-reverse; /* Reverse the order */
    overflow-y: auto;
    padding: 10px;
    position: relative;
}

.message {
    align-items: flex-start;
}
.message p {
  margin: 5px 0;
  word-break: break-word;
}

.message-timestamp {
  font-size: 0.7rem;
  color: #aaa;
  display: block;
  text-align: right;
}

.message-status {
  margin-left: 5px;
  font-size: 0.7rem;
}

.text-secondary {
  color: #aaa;
}

.text-primary {
  color: #4CAF50;
}

/* Message Input */
.message-input {
  padding: 15px;
  background-color: #252525;
  border-top: 1px solid #333;
  display: flex;
  align-items: center;
}

.message-input textarea{
  flex-grow: 1;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #444;
  background-color: #333;
  color: #fff;
  resize: none;
  min-height: 50px;
  max-height: 150px;
  margin-right: 10px;
}

.message-input textarea:focus {
  outline: none;
  border-color: #4CAF50;
}

.message-input button {
  padding: 10px 15px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.message-input button:hover {
  background-color: #45a049;
}


/* Group Details */
.group-details {
  width: 250px;
  background-color: #252525;
  border-left: 1px solid #333;
  padding: 15px;
  overflow-y: auto;
}

.group-details h4, .group-details h5 {
  color: #4CAF50;
  margin-top: 0;
}

.group-details ul {
  list-style: none;
  padding: 0;
}

.group-details li {
  padding: 5px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.remove-participant-btn {
  background: none;
  border: none;
  color: #ff5252;
  cursor: pointer;
  padding: 2px 5px;
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background-color: #252525;
  border-radius: 8px;
  padding: 20px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.modal h3 {
  margin-top: 0;
  color: #4CAF50;
}

.modal label {
  display: block;
  margin: 10px 0 5px;
  color: #aaa;
}

.modal input[type="text"] {
  width: 100%;
  padding: 8px;
  border-radius: 4px;
  border: 1px solid #444;
  background-color: #333;
  color: #fff;
}

.user-list {
  margin: 15px 0;
  max-height: 200px;
  overflow-y: auto;
}

.user-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #333;
}

.add-participant-btn {
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 50%;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.selected-participants {
  margin: 15px 0;
}

.participant-tag {
  display: inline-block;
  background-color: #333;
  padding: 5px 10px;
  border-radius: 15px;
  margin-right: 5px;
  margin-bottom: 5px;
}

.participant-tag button {
  background: none;
  border: none;
  color: #ff5252;
  margin-left: 5px;
  cursor: pointer;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.modal-actions button {
  padding: 8px 15px;
  margin-left: 10px;
  border-radius: 4px;
  cursor: pointer;
}

.modal-actions button:first-child {
  background-color: #555;
  color: #fff;
  border: none;
}

.modal-actions button:last-child {
  background-color: #4CAF50;
  color: white;
  border: none;
}

/* Post/Attachment Styles */
.post-container {
  margin-top: 10px;
}

.post-image {
  max-width: 100%;
  max-height: 300px;
  border-radius: 5px;
}

/* Buttons */
button {
  transition: all 0.2s;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.create-group-btn {
  margin: 15px;
  padding: 10px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.create-group-btn:hover {
  background-color: #45a049;
}
.upload-input{
    background-color: #1a1a1a;
    width: 200px;
}
/* Loading Indicator */
.loading-indicator {
  text-align: center;
  padding: 10px;
  color: #aaa;
  font-style: italic;
}
.sender-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 10px;
    flex-shrink: 0;
}

.sender-avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-avatar {
    font-size: 18px;
    font-weight: bold;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f0f0f0;
}
.avatar-container {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f0f0f0;
    margin-right: 10px;
}
.time-status{
    display: flex;
}
/* Responsive Design */
@media (max-width: 768px) {
  .chat-container {
    flex-direction: column;
    height: auto;
    min-height: 100vh;
  }

  .conversation-list {
    width: 100%;
    height: 300px;
    border-right: none;
    border-bottom: 1px solid #333;
  }

  .message-view {
    height: calc(100vh - 300px);
  }

  .group-details {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 80%;
    max-width: 300px;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    z-index: 100;
  }

  .group-details.active {
    transform: translateX(0);
  }

  .message {
    max-width: 85%;
  }
}

@media (max-width: 480px) {
  .message-input {
    flex-direction: column;
    align-items: stretch;
  }

  .message-input textarea {
    margin-right: 0;
    margin-bottom: 10px;
  }

  .message-input button {
    width: 100%;
  }

  .modal {
    width: 95%;
    padding: 15px;
  max-height: 90vh;
  }
}
</style>
