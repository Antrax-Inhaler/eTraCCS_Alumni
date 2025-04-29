
<style scoped>
:root {
      --primary: #ff8c00;
      --primary-light: rgba(255, 140, 0, 0.1);
      --text-primary: #ffffff;
      --text-secondary: #cccccc;
      --bg-dark: #1a1a1a;
      --bg-darker: #121212;
      --card-bg: rgba(40, 40, 40, 0.7);
      --card-border: rgba(255, 255, 255, 0.1);
      --success: #4CAF50 !important;
      --warning: #FFC107;
      --danger: #F44336;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-darker);
      color: var(--text-primary) !important;
      background-image: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80');
      background-size: cover;
      background-attachment: fixed;
      min-height: 100vh;
    }

    .app-container {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Sidebar Navigation */
    .sidebar {
      background: var(--card-bg);
      border-bottom: 1px solid var(--card-border);
      backdrop-filter: blur(12px);
      padding: 10px 0;
      transition: all 0.3s;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .sidebar-header {
      padding: 0 15px 10px;
      border-bottom: 1px solid var(--card-border);
      margin-bottom: 10px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 18px;
      font-weight: 700;
      color: var(--primary);
    }

    .logo-icon {
      width: 30px;
      height: 30px;
      background: var(--primary);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .nav-menu {
      padding: 0 10px;
      display: flex;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none; /* Firefox */
    }

    .nav-menu::-webkit-scrollbar {
      display: none; /* Chrome/Safari */
    }

    .nav-group {
      display: flex;
    }

    .nav-title {
      display: none;
    }

    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
      padding: 8px 12px;
      border-radius: 8px;
      margin-right: 5px;
      cursor: pointer;
      transition: all 0.2s;
      min-width: 70px;
      font-size: 12px;
      text-align: center;
    }

    .nav-item:hover {
      background: var(--primary-light);
      color: var(--primary);
    }

    .nav-item.active {
      background: var(--primary-light);
      color: var(--primary);
      font-weight: 600;
    }

    .nav-item i {
      font-size: 16px;
      width: 24px;
      text-align: center;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      padding: 15px;
      overflow-y: auto;
    }

    .header {
      display: flex;
      flex-direction: column;
      margin-bottom: 20px;
    }

    .page-title {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 15px;
    }

    .user-menu {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .search-bar {
      position: relative;
      width: 100%;
      margin-bottom: 15px;
    }

    .search-bar input {
      width: 100%;
      padding: 10px 15px 10px 40px;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid var(--card-border);
      border-radius: 8px;
      color: var(--text-primary);
      font-size: 14px;
    }

    .search-bar i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-secondary);
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 10px;
      justify-content: flex-end;
      color: white !important;
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
    }

    /* Dashboard Cards */
    .stats-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-bottom: 25px;
    }

    .stat-card {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      backdrop-filter: blur(12px);
      border-radius: 10px;
      padding: 15px;
      transition: all 0.3s;
    }

    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .stat-title {
      font-size: 12px;
      color: var(--text-secondary);
      margin-bottom: 8px;

    }

    .stat-value {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 5px;
      color: var(--text-primary);

    }

    .stat-change {
      font-size: 11px;
      display: flex;
      align-items: center;
      gap: 3px;
    }

    .stat-change.up {
      color: var(--success);
      color: #4CAF50;
    }

    .stat-change.down {
      color: var(--danger);
      color: #F44336;
    }

    /* Alumni Table */
    .data-section {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      backdrop-filter: blur(12px);
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
    }

    .section-header {
      display: flex;
      flex-direction: column;
      margin-bottom: 15px;
    }

    .section-title {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--text-primary) !important;

    }

    .table-controls {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }

    .btn {
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 500;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
      border: none;
      white-space: nowrap;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: #e67e00;
      transform: translateY(-1px);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary-light);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px;
      color: var(--text-secondary);
    }

    th, td {
      padding: 10px 12px;
      text-align: left;
      border-bottom: 1px solid var(--card-border);
      font-size: 13px;
    }

    th {
      font-weight: 600;
      color: var(--text-secondary);
      font-size: 13px;
    }

    tr:hover td {
      background: rgba(255, 255, 255, 0.05);
    }

    .status {
      display: inline-block;
      padding: 3px 6px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 500;
    }
    .chart-title{
  color: var(--text-secondary);
}
    .status.active {
      background: rgba(76, 175, 80, 0.2);
      color: var(--success);
    }

    .status.inactive {
      background: rgba(244, 67, 54, 0.2);
      color: var(--danger);
    }

    .action-btn {
      background: none;
      border: none;
      color: var(--text-secondary);
      cursor: pointer;
      margin: 0 3px;
      transition: all 0.2s;
      font-size: 13px;
    }

    .action-btn:hover {
      color: var(--primary);
      transform: scale(1.05);
    }

    /* Alumni Profile Cards */
    .alumni-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 15px;
    }

    .profile-card {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      backdrop-filter: blur(12px);
      border-radius: 10px;
      overflow: hidden;
      transition: all 0.3s;
    }

    .profile-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .profile-banner {
      height: 80px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .profile-content {
      padding: 15px;
      position: relative;
    }

    .profile-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--card-bg);
      position: absolute;
      top: -30px;
      left: 15px;
    }

    .profile-name {
      font-size: 16px;
      font-weight: 600;
      margin-top: 30px;
      margin-bottom: 5px;
    }

    .profile-meta {
      font-size: 12px;
      color: var(--text-secondary);
      margin-bottom: 12px;
    }

    .profile-stats {
      display: flex;
      gap: 10px;
      margin-top: 12px;
    }

    .profile-stat {
      text-align: center;
      flex: 1;
    }

    .stat-number {
      font-weight: 600;
      font-size: 14px;
    }

    .stat-label {
      font-size: 11px;
      color: var(--text-secondary);
    }

    /* Responsive Improvements */
    @media (min-width: 576px) {
      .header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
      }
      
      .user-menu {
        flex-direction: row;
        align-items: center;
      }
      
      .search-bar {
        width: 250px;
        margin-bottom: 0;
        margin-right: 15px;
      }
      
      .section-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
      }
      
      .section-title {
        margin-bottom: 0;
      }
      
      .table-controls {
        margin-top: 0;
      }
    }

    @media (min-width: 768px) {
      .app-container {
        flex-direction: row;
      }
      
      .sidebar {
        width: 80px;
        height: 100vh;
        position: sticky;
        top: 0;
        border-right: 1px solid var(--card-border);
        border-bottom: none;
      }
      
      .nav-menu {
        display: block;
        overflow-x: visible;
        padding: 0 10px;
      }
      
      .nav-group {
        display: block;
      }
      
      .nav-item {
        flex-direction: row;
        justify-content: flex-start;
        padding: 10px;
        min-width: auto;
        font-size: 14px;
        margin-bottom: 5px;
        margin-right: 0;
        text-align: left;
      }
      
      .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      }
    }

    @media (min-width: 992px) {
      .sidebar {
        width: 280px;
      }
      
      .logo span, .nav-item span {
        display: inline;
      }
      
      .nav-title {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-secondary);
        margin: 20px 0 10px 10px;
      }
      
      .main-content {
        padding: 20px;
      }
      
      .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      }
    }

    /* Print styles */
    @media print {
      .sidebar, .search-bar, .table-controls, .action-btn {
        display: none !important;
      }
      
      body {
        background: white !important;
        color: black !important;
      }
      
      .main-content {
        padding: 0 !important;
      }
      
      table {
        min-width: 100% !important;
      }
      
      th, td {
        color: black !important;
      }
    }
    .status.active {
      background: rgba(76, 175, 80, 0.2);
      color: #4CAF50 !important;
    }

    .status.inactive {
      background: rgba(244, 67, 54, 0.2);
      color: #F44336 !important;
    }

</style>
<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

const props = defineProps({
  stats: Object,
  recentAlumni: Array,
  employmentData: Object,
  educationData: Object,
  featuredAlumni: Array,
  recentContent: Array
});

// Chart references
const employmentChartCanvas = ref(null);
const educationChartCanvas = ref(null);

onMounted(() => {
  // Create employment chart
  if (employmentChartCanvas.value) {
    new Chart(employmentChartCanvas.value.getContext('2d'), {
      type: 'pie',
      data: {
        labels: props.employmentData.labels,
        datasets: [{
          data: props.employmentData.rates,
          backgroundColor: ['#ff8c00', '#F44336', '#4CAF50', '#2196F3'],
          borderWidth: 0
        }]
      },
      options: getChartOptions()
    });
  }

  // Create education chart
  if (educationChartCanvas.value) {
    new Chart(educationChartCanvas.value.getContext('2d'), {
      type: 'bar',
      data: {
        labels: props.educationData.degrees,
        datasets: [{
          label: 'Additional Degrees',
          data: props.educationData.counts,
          backgroundColor: '#ff8c00',
          borderWidth: 0
        }]
      },
      options: getChartOptions()
    });
  }
});

const getChartOptions = () => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: '#ffffff',
        font: {
          size: 12
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0,0,0,0.8)',
      titleColor: '#ffffff',
      bodyColor: '#ffffff'
    }
  }
});

const getRandomGradient = () => {
  const gradients = [
    '#667eea, #764ba2',
    '#f093fb, #f5576c',
    '#5ee7df, #b490ca',
    '#ff758c, #ff7eb3',
    '#a18cd1, #fbc2eb'
  ];
  return gradients[Math.floor(Math.random() * gradients.length)];
};
</script>

<template>
  <Head title="Admin Dashboard" />
  
  <AdminLayout title="Dashboard">
    <!-- Stats Cards -->
    <div class="stats-cards">
      <div class="stat-card">
        <div class="stat-title">Total Alumni</div>
        <div class="stat-value">{{ stats.totalAlumni.toLocaleString() }}</div>
        <div class="stat-change" :class="stats.alumniGrowth >= 0 ? 'up' : 'down'">
          <i class="fas" :class="stats.alumniGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i> 
          {{ Math.abs(stats.alumniGrowth) }}% from last year
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Active Users</div>
        <div class="stat-value">{{ stats.activeUsers.toLocaleString() }}</div>
        <div class="stat-change" :class="stats.activeGrowth >= 0 ? 'up' : 'down'">
          <i class="fas" :class="stats.activeGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i> 
          {{ Math.abs(stats.activeGrowth) }}% from last month
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Employment Rate</div>
        <div class="stat-value">{{ stats.employmentRate }}%</div>
        <div class="stat-change" :class="stats.employmentChange >= 0 ? 'up' : 'down'">
          <i class="fas" :class="stats.employmentChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i> 
          {{ Math.abs(stats.employmentChange) }}% from last year
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-title">New Registrations</div>
        <div class="stat-value">{{ stats.newRegistrations }}</div>
        <div class="stat-change" :class="stats.registrationGrowth >= 0 ? 'up' : 'down'">
          <i class="fas" :class="stats.registrationGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i> 
          {{ Math.abs(stats.registrationGrowth) }}% from last month
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Employment Statistics</h2>
      </div>
      <div class="chart-grid">
        <div class="chart-container">
          <h3 class="chart-title">Current Employment Status</h3>
          <div class="chart-wrapper">
            <canvas ref="employmentChartCanvas"></canvas>
          </div>
        </div>
        <div class="chart-container">
          <h3 class="chart-title">Additional Degrees Earned</h3>
          <div class="chart-wrapper">
            <canvas ref="educationChartCanvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Alumni Table -->
    <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Recent Alumni</h2>
        <div class="table-controls">
          <button class="btn btn-outline">
            <i class="fas fa-filter"></i> Filter
          </button>
          <!-- <Link :href="route('admin.alumni.create')" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Alumni
          </Link> -->
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Graduation Year</th>
            <th>Degree</th>
            <th>Current Position</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="alumni in recentAlumni" :key="alumni.id">
            <td>
              <div class="user-cell" style="display: flex; align-items: center; gap: 10px;">
                <img :src="alumni.photo || 'https://randomuser.me/api/portraits/men/'+(Math.floor(Math.random() * 100)+1)+'.jpg'" 
                     class="user-avatar" alt="Profile">
                {{ alumni.name }}
              </div>
            </td>
            <td>{{ alumni.graduation_year || 'N/A' }}</td>
            <td>{{ alumni.degree || 'N/A' }}</td>
            <td>
              <span v-if="alumni.current_job">{{ alumni.current_job.title }} at {{ alumni.current_job.company || 'N/A' }}</span>
              <span v-else class="text-muted">Not specified</span>
            </td>
            <td>
              <span class="status" :class="alumni.is_active ? 'active' : 'inactive'">
                {{ alumni.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
              <!-- <Link :href="route('admin.alumni.show', alumni.id)" class="action-btn" title="View">
                <i class="fas fa-eye"></i>
              </Link>
              <Link :href="route('admin.alumni.edit', alumni.id)" class="action-btn" title="Edit">
                <i class="fas fa-edit"></i>
              </Link> -->
              <button class="action-btn" title="Message">
                <i class="fas fa-envelope"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Featured Alumni -->
    <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Featured Alumni</h2>
        <!-- <Link :href="route('admin.alumni.index')" class="btn btn-outline">
          View All
        </Link> -->
      </div>
      <div class="alumni-grid">
        <div class="profile-card" v-for="alumni in featuredAlumni" :key="alumni.id">
          <div class="profile-banner" :style="'background: linear-gradient(135deg, ' + getRandomGradient() + ')'"></div>
          <div class="profile-content">
            <img :src="alumni.photo || 'https://randomuser.me/api/portraits/men/'+(Math.floor(Math.random() * 100)+1)+'.jpg'" 
                 class="profile-avatar" alt="Profile">
            <h3 class="profile-name">{{ alumni.name }}</h3>
            <div class="profile-meta">
              <div>{{ alumni.degree || 'N/A' }}, {{ alumni.graduation_year || 'N/A' }}</div>
              <div v-if="alumni.location">{{ alumni.location.city }}, {{ alumni.location.country }}</div>
            </div>
            <div class="profile-stats">
              <div class="profile-stat">
                <div class="stat-number">{{ alumni.content_items || 0 }}</div>
                <div class="stat-label">Posts</div>
              </div>
              <div class="profile-stat">
                <div class="stat-number">{{ alumni.connections || 0 }}</div>
                <div class="stat-label">Connections</div>
              </div>
              <div class="profile-stat">
                <div class="stat-number">{{ alumni.job_related ? 'Yes' : 'No' }}</div>
                <div class="stat-label">Related Job</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Content -->
    <div class="data-section" v-if="recentContent && recentContent.length">
      <div class="section-header">
        <h2 class="section-title">Recent Content</h2>
      </div>
      <table>
        <thead>
          <tr>
            <th>Type</th>
            <th>Title</th>
            <th>Date</th>
            <th>Author</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="content in recentContent" :key="content.id">
            <td>
              <span class="content-type" :class="content.type">{{ content.type }}</span>
            </td>
            <td>{{ content.title || 'Untitled' }}</td>
            <td>{{ content.date || 'N/A' }}</td>
            <td>{{ content.user }}</td>
            <td>{{ content.created_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>
