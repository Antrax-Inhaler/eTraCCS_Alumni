<template>
    <div class="follow-section">
      <h3 class="section-title">Who to follow</h3>
      
      <div v-if="loading" class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
      </div>
      
      <div v-else>
        <div v-for="user in suggestedUsers" :key="user.id" class="follow-user">
          <div class="follow-user-info">
            <img :src="user.profile_photo_url || 'https://randomuser.me/api/portraits/men/22.jpg'" 
                 class="post-avatar" alt="Profile">
            <div>
              <div class="post-username">{{ user.full_name }}</div>
              <div class="post-userhandle">@{{ user.name }}</div>
            </div>
          </div>
          <button 
            class="follow-button"
            :class="{ 'following': user.is_following }"
            @click="toggleFollow(user.id)"
          >
            {{ user.is_following ? 'Following' : 'Follow' }}
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  
  const suggestedUsers = ref([]);
  const loading = ref(true);
  
  const fetchSuggestedUsers = async () => {
    try {
      loading.value = true;
      const response = await axios.get('/api/users/suggestions');
      suggestedUsers.value = response.data;
    } catch (error) {
      console.error('Error fetching suggestions:', error);
    } finally {
      loading.value = false;
    }
  };
  
  const toggleFollow = async (userId) => {
    try {
      const response = await axios.post(`/api/users/${userId}/toggle-follow`);
      const updatedUser = suggestedUsers.value.find(u => u.id === userId);
      if (updatedUser) {
        updatedUser.is_following = !updatedUser.is_following;
      }
    } catch (error) {
      console.error('Error toggling follow:', error);
    }
  };
  
  onMounted(() => {
    fetchSuggestedUsers();
  });
  </script>
  
  <style scoped>
  .follow-section {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
  }
  
  .section-title {
    font-size: 1.2rem;
    margin-bottom: 16px;
    color: var(--text-primary);
  }
  
  .follow-user {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--card-border);
  }
  
  .follow-user:last-child {
    border-bottom: none;
  }
  
  .follow-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .post-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
  }
  
  .post-username {
    font-weight: 600;
    color: var(--text-primary);
  }
  
  .post-userhandle {
    font-size: 0.9rem;
    color: var(--text-secondary);
  }
  
  .follow-button {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 6px 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .follow-button:hover {
    background: #e67e00;
  }
  
  .follow-button.following {
    background: transparent;
    color: var(--primary);
    border: 1px solid var(--primary);
  }
  
  .loading-spinner {
    display: flex;
    justify-content: center;
    padding: 20px;
    color: var(--primary);
  }
  </style>