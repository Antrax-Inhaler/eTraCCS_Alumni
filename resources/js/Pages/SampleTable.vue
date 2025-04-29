<template>
  <div>
    <!-- User Profile Section -->
    <div v-if="user" class="user-profile">
      <div class="profile-picture">
        <img :src="user.profile_photo_url" :alt="user.name" class="avatar">
      </div>
      <div class="profile-info">
        <h2>{{ user.name }}</h2>
        <p v-if="user.first_name || user.last_name">
          {{ user.first_name }} {{ user.middle_initial }} {{ user.last_name }}
        </p>
        <p>{{ user.email }}</p>
      </div>
    </div>

    <h1>Sample Table Data</h1>
    <table border="1">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Age</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>{{ item.id }}</td>
          <td>{{ item.name }}</td>
          <td>{{ item.age }}</td>
          <td>{{ item.email }}</td>
        </tr>
        <tr v-if="isLoading">
          <td colspan="4">Loading...</td>
        </tr>
      </tbody>
    </table>

    <!-- Fallback for no data -->
    <p v-if="items.length === 0 && !isLoading">No data available</p>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const isLoading = ref(false);
const user = ref(null); // Store user data

const fetchData = async () => {
  if (isLoading.value || currentPage.value > totalPages.value) return;

  try {
    isLoading.value = true;
    const response = await axios.get(`/api/sample-tb?page=${currentPage.value}`);
    
    // Handle both data and user info
    items.value = [...items.value, ...response.data.data.data];
    currentPage.value = response.data.data.current_page;
    totalPages.value = response.data.data.last_page;
    
    // Set user data if not already set (only on first page load)
    if (currentPage.value === 1) {
      user.value = response.data.user;
    }
  } catch (error) {
    console.error('Error fetching data:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleScroll = () => {
  const { scrollTop, clientHeight, scrollHeight } = document.documentElement;

  if (scrollTop + clientHeight >= scrollHeight - 50) {
    currentPage.value += 1;
    fetchData();
  }
};

onMounted(() => {
  fetchData();
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 8px;
  text-align: left;
}
th {
  background-color: #f2f2f2;
}

/* Loading indicator */
td[colspan="4"] {
  text-align: center;
  font-style: italic;
  color: gray;
}

/* User profile styles */
.user-profile {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.profile-picture {
  margin-right: 20px;
}

.avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-info h2 {
  margin: 0 0 5px 0;
  color: #333;
}

.profile-info p {
  margin: 5px 0;
  color: #666;
}
</style>