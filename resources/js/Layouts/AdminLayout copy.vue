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
      --success: #4CAF50;
      --warning: #FFC107;
      --danger: #F44336;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body{
      background-image: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80');

    }
    .body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-darker);
      color: var(--text-primary);
      background-image: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80');
      background-size: cover;
      background-attachment: fixed;
      height: 100vh;
      display: block !important;
      justify-content: flex-end !important;
      position: fixed;
      
    }
    .background-blur {
      position: fixed;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(4px);
      background-color: rgba(0, 0, 0, 0.3); /* subtle dark overlay */
      z-index: -1;
    }

    .app-container {
      display: flex;
      flex-direction: column;
      height: 100vh;
    }

    /* Toggle Button */
.sidebar-toggle {
  position: fixed;
  top: 10px;
  left: 10px;
  z-index: 101;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  transition: all 0.3s;
}

.sidebar-toggle:hover {
  background: var(--primary-dark);
}

/* Sidebar */
.sidebar {
  background: var(--card-bg);
  backdrop-filter: blur(12px);
  padding: 10px 0;
  transition: all 0.3s ease;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 100;
  border-right: 1px solid var(--card-border);
  transform: translateX(-100%);
  width: 280px;
}

.sidebar.sidebar-open {
  transform: translateX(0);
}

.sidebar-header {
  padding: 20px 15px;
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
  overflow-y: auto;
  height: calc(100vh - 80px);
}

.nav-group {
  margin-bottom: 15px;
}

.nav-title {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text-secondary);
  margin: 20px 0 10px 10px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 15px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
  color: var(--text-primary);
  flex-direction: row;
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
  padding: 20px;
  margin-left: 0;
  transition: margin-left 0.3s;
}

/* Responsive Styles */
@media (min-width: 768px) {
  .sidebar {
    transform: translateX(0);
    width: 80px;
  }
  
  .sidebar:not(.sidebar-open) .logo span,
  .sidebar:not(.sidebar-open) .nav-item span,
  .sidebar:not(.sidebar-open) .nav-title {
    display: none;
  }
  
  .sidebar:not(.sidebar-open) .nav-item {
    justify-content: center;
  }
  
  .main-content {
    margin-left: 80px;
  }
  
  .sidebar-toggle {
    display: none;
  }
}

@media (min-width: 992px) {
  .sidebar {
    width: 280px;
  }
  
  .sidebar .logo span,
  .sidebar .nav-item span,
  .sidebar .nav-title {
    display: block;
    text-shadow: 2px 2px 4px rgb(0 0 0);
  }
  
  .sidebar .nav-item {
    justify-content: flex-start;
  }
  
  .main-content {
    margin-left: 280px;
  }
}

@media (max-width: 767px) {
  .sidebar.sidebar-open {
    width: 280px;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  }
  
  .sidebar.sidebar-open ~ .main-content {
    margin-left: 280px;
  }
}

    /* Main Content */
    .main-content {
      flex: 1;
      padding-top: 15px;
      overflow-y: auto;
      height: 100vh;
    }

    .header {
      display: flex;
      flex-direction: column;
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
      color: black;
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
    }

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
    }

    .stat-change {
      font-size: 11px;
      display: flex;
      align-items: center;
      gap: 3px;
    }

    .stat-change.up {
      color: var(--success);
    }

    .stat-change.down {
      color: var(--danger);
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
      
      .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      }
    }

    @media (min-width: 992px) {
      
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
</style>

<template>
  <div class="background-blur"></div>
  <div class="app-container">
       <button class="sidebar-toggle" @click="toggleSidebar">
      <i class="fas" :class="isSidebarOpen ? 'fa-times' : 'fa-bars'"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-open': isSidebarOpen }">
      <div class="sidebar-header">
        <div class="logo">
          <div class="logo-icon">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <span>eTraCCS Admin</span>
        </div>
      </div>

      <nav class="nav-menu">
  <!-- Dashboard -->
  <div class="nav-group">
    <div class="nav-title">Main</div>
    <Link href="/admin/dashboard" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/dashboard') }">
      <i class="fas fa-home"></i>
      <span>Dashboard</span>
    </Link>
  </div>

  <!-- Alumni Management -->
  <div class="nav-group">
    <div class="nav-title">Alumni</div>
    <Link href="/admin/alumni" class="nav-item" :class="{ 'active': $page.url === '/admin/alumni' }">
      <i class="fas fa-users"></i>
      <span>All Alumni</span>
    </Link>
    <Link href="/admin/alumni/verification" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/alumni/verification') }">
      <i class="fas fa-check-circle"></i>
      <span>Profile Verification</span>
    </Link>
  </div>

  <!-- Employment & Education -->
  <div class="nav-group">
    <div class="nav-title">Tracking</div>
    <Link href="/admin/educational-tracking" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/educational-tracking') }">
      <i class="fas fa-graduation-cap"></i>
      <span>Educational Tracking</span>
    </Link>
    <Link href="/admin/employability-analytics" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/employability-analytics') }">
      <i class="fas fa-chart-line"></i>
      <span>Employability Analytics</span>
    </Link>
  
  </div>

  <!-- Reports Section -->
  <div class="nav-group">
    <div class="nav-title">Reports</div>
    <Link href="/reports/graduate-profiles" class="nav-item" :class="{ 'active': $page.url.startsWith('/reports/graduate-profiles') }">
      <i class="fas fa-user-graduate"></i>
      <span>Graduate Profiles</span>
    </Link>
    <Link href="/reports/advancement" class="nav-item" :class="{ 'active': $page.url.startsWith('/reports/advancement') }">
      <i class="fas fa-arrow-up"></i>
      <span>Advancement</span>
    </Link>
    <Link href="/reports/employment-status" class="nav-item" :class="{ 'active': $page.url.startsWith('/reports/employment-status') }">
      <i class="fas fa-briefcase"></i>
      <span>Employment Status</span>
    </Link>
    <Link href="/reports/employability-metrics" class="nav-item" :class="{ 'active': $page.url.startsWith('/reports/employability-metrics') }">
      <i class="fas fa-chart-bar"></i>
      <span>Employability Metrics</span>
    </Link>
    <Link href="/reports/competency-mapping" class="nav-item" :class="{ 'active': $page.url.startsWith('/reports/competency-mapping') }">
      <i class="fas fa-project-diagram"></i>
      <span>Competency Mapping</span>
    </Link>
  </div>

  <!-- GIS & Analytics -->
  <div class="nav-group">
    <div class="nav-title">Analytics</div>
    <Link href="/admin/mapping" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/mapping') }">
      <i class="fas fa-map-marked-alt"></i>
      <span>GIS Mapping</span>
    </Link>
    <Link href="/admin/employability" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/employability') }">
      <i class="fas fa-chart-line"></i>
      <span>Employability Index</span>
    </Link>
  </div>

  <!-- Content Management -->
  <div class="nav-group">
    <div class="nav-title">Content</div>
    <Link href="/admin/success-stories" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/success-stories') }">
      <i class="fas fa-star"></i>
      <span>Success Stories</span>
    </Link>
    <Link href="/admin/jobs" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/jobs') }">
      <i class="fas fa-search-dollar"></i>
      <span>Job Postings</span>
    </Link>
    <Link href="/admin/events" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/events') }">
      <i class="fas fa-calendar-alt"></i>
      <span>Events</span>
    </Link>
  </div>

  <!-- System -->
  <div class="nav-group">
    <div class="nav-title">System</div>
    <Link href="/admin/settings" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/settings') }">
      <i class="fas fa-cog"></i>
      <span>Settings</span>
    </Link>
    <Link href="/admin/users" class="nav-item" :class="{ 'active': $page.url.startsWith('/admin/users') }">
      <i class="fas fa-user-shield"></i>
      <span>Admin Users</span>
    </Link>
  </div>
</nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <header class="header">
        <h1 class="page-title">{{ title }}</h1>
        <div class="user-menu">
          <!-- <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search...">
          </div> -->
          <div class="user-profile">
            <img :src="user.avatar" class="user-avatar" alt="Profile">
            <span>{{ user.name }}</span>
            <button @click="logout" class="action-btn ml-2" title="Logout">
              <i class="fas fa-sign-out-alt"></i>
            </button>
          </div>
        </div>
      </header>

      <slot />
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  title: String
})

const user = computed(() => usePage().props.auth.user)

function logout() {
  router.post('/admin/logout')
}
const isSidebarOpen = ref(false);
const windowWidth = ref(window.innerWidth);

const isSidebarExpanded = computed(() => {
  return windowWidth.value >= 992 || isSidebarOpen.value;
});

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const handleResize = () => {
  windowWidth.value = window.innerWidth;
  // Auto-close sidebar when resizing to larger screens
  if (windowWidth.value >= 992) {
    isSidebarOpen.value = false;
  }
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
});
</script>
