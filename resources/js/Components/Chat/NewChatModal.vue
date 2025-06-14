// In your NewChatModal.vue
<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';

const emit = defineEmits(['close', 'start-chat']);

const searchQuery = ref('');
const searchResults = ref([]);
const isLoading = ref(false);

watch(searchQuery, debounce(async (query) => {
  if (!query) {
    searchResults.value = [];
    return;
  }
  
  try {
    isLoading.value = true;
    const response = await axios.get(route('chat.search'), {
      params: { q: query }
    });
    searchResults.value = response.data.map(user => ({
      ...user,
      avatar: user.profile_photo_url || '/default-avatar.png'
    }));
  } catch (error) {
    console.error('Error searching users:', error);
    searchResults.value = [];
  } finally {
    isLoading.value = false;
  }
}, 300));

function startChatWithUser(user) {
  emit('start-chat', user);
  emit('close');
}
</script>

<template>
  <div class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3>New Chat</h3>
        <button @click="$emit('close')" class="close-button">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="search-container">
          <input v-model="searchQuery" type="text" placeholder="Search users..." class="search-input">
        </div>
        
        <div v-if="isLoading" class="loading">
          <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>
        
        <div v-else-if="searchResults.length" class="user-list">
          <div 
            v-for="user in searchResults" 
            :key="user.id" 
            class="user-item" 
            @click="startChatWithUser(user)"
          >
            <img :src="user.avatar" :alt="user.name" class="user-avatar">
            <div class="user-details">
              <div class="user-name">{{ user.name }}</div>
              <div class="user-email">{{ user.email }}</div>
            </div>
            <div v-if="user.is_online" class="online-indicator"></div>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <i class="fas fa-users"></i>
          <p v-if="searchQuery">No users found for "{{ searchQuery }}"</p>
          <p v-else>Start typing to search for users</p>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="$emit('close')" class="cancel-button">Cancel</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
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

.modal-container {
  background-color: #fff;
  border-radius: 8px;
  width: 100%;
  max-width: 400px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
}

.close-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #616161;
}

.modal-body {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
}

.search-container {
  margin-bottom: 16px;
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  font-size: 14px;
}

.loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 16px;
  color: #757575;
}

.user-list {
  display: flex;
  flex-direction: column;
}

.user-item {
  display: flex;
  align-items: center;
  padding: 12px;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.2s;
  position: relative;
}

.user-item:hover {
  background-color: #f5f5f5;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 12px;
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 500;
  margin-bottom: 2px;
}

.user-email {
  font-size: 13px;
  color: #757575;
}

.online-indicator {
  width: 10px;
  height: 10px;
  background-color: #4caf50;
  border-radius: 50%;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px;
  color: #9e9e9e;
}

.empty-state i {
  font-size: 32px;
  margin-bottom: 8px;
}

.modal-footer {
  padding: 12px 16px;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
}

.cancel-button {
  background-color: #f5f5f5;
  color: #424242;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.cancel-button:hover {
  background-color: #e0e0e0;
}
</style>