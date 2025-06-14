<template>
  <Head title="Admin Dashboard" />
  
  <AdminLayout title="Dashboard">
    <!-- Stats Cards -->
     <div class="filters-section">
      <div class="filter-controls">
        <div class="filter-group">
          <label for="gender-filter">Filter by Gender:</label>
          <select id="gender-filter" v-model="filters.gender" @change="applyFilters">
            <option value="">All Genders</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label for="salary-filter">Filter by Salary Range:</label>
          <select id="salary-filter" v-model="filters.salary_range" @change="applyFilters">
            <option value="">All Salary Ranges</option>
            <option value="below_10k">Below 10k</option>
            <option value="10k_15k">10k-15k</option>
            <option value="15k_20k">15k-20k</option>
            <option value="20k_30k">20k-30k</option>
            <option value="above_30k">Above 30k</option>
          </select>
        </div>
        
        <button @click="resetFilters" class="reset-btn">Reset Filters</button>
      </div>
    </div>

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

    <!-- Demographics Section -->
    <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Alumni Demographics</h2>
      </div>
      <div class="chart-grid">
        <!-- Gender Distribution -->
        <div class="chart-container">
          <h3 class="chart-title">Gender Distribution</h3>
          <div class="chart-wrapper">
            <canvas ref="genderChartCanvas"></canvas>
          </div>
        </div>
        
        <!-- Graduation Years -->
        <div class="chart-container">
          <h3 class="chart-title">Graduation Years</h3>
          <div class="chart-wrapper">
            <canvas ref="graduationYearChartCanvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Education Section -->
    <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Educational Background</h2>
      </div>
      <div class="chart-grid">
        <!-- Educational Profiles -->
        <div class="chart-container">
          <h3 class="chart-title">Primary Degrees</h3>
          <div class="chart-wrapper">
            <canvas ref="educationProfileChartCanvas"></canvas>
          </div>
        </div>
        
        <!-- BSIT Reasons -->
        <div class="chart-container">
          <h3 class="chart-title">Reasons for Taking BSIT</h3>
          <div class="chart-wrapper">
            <canvas ref="bsitReasonsChartCanvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Graduate Studies Section -->
    <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Graduate Studies</h2>
      </div>
      <div class="chart-grid">
        <!-- Graduate Degrees -->
        <div class="chart-container">
          <h3 class="chart-title">Graduate Degrees Pursued</h3>
          <div class="chart-wrapper">
            <canvas ref="graduateDegreesChartCanvas"></canvas>
          </div>
        </div>
        
        <!-- Reasons for Graduate Studies -->
        <div class="chart-container">
          <h3 class="chart-title">Reasons for Graduate Studies</h3>
          <div class="chart-wrapper">
            <canvas ref="graduateReasonsChartCanvas"></canvas>
          </div>
        </div>
      </div>
      <div class="additional-stat">
        <div class="stat-value">{{ demographics.graduateStudies.currentlyStudying }}</div>
        <div class="stat-title">Alumni Currently Pursuing Graduate Studies</div>
      </div>
    </div>

    <!-- Employment Section -->
     <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Employment Statistics</h2>
        <div class="filter-note" v-if="filters.gender || filters.salary_range">
          Showing data filtered by: 
          <span v-if="filters.gender">{{ filters.gender }}</span>
          <span v-if="filters.gender && filters.salary_range"> and </span>
          <span v-if="filters.salary_range">{{ formatSalaryRange(filters.salary_range) }}</span>
        </div>
      </div>
      <div class="chart-grid">
        <!-- Time to First Job -->
        <div class="chart-container">
          <h3 class="chart-title">Time to First Job After Graduation</h3>
          <div class="chart-wrapper">
            <canvas ref="timeToJobChartCanvas"></canvas>
          </div>
        </div>
        
        <!-- Job Hunting Difficulties -->
        <div class="chart-container">
          <h3 class="chart-title">Job Hunting Difficulties</h3>
          <div class="chart-wrapper">
            <canvas ref="jobDifficultiesChartCanvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Salary Section -->
    <div class="data-section">
      <div class="section-header">
        <h2 class="section-title">Starting Salaries</h2>
        <div class="filter-note" v-if="filters.gender">
          Showing data filtered by: {{ filters.gender }}
        </div>
      </div>
      <div class="chart-container">
        <div class="chart-wrapper">
          <canvas ref="startingSalaryChartCanvas"></canvas>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

const props = defineProps({
  stats: Object,
  demographics: Object,
  filters: Object
});
// Chart references
const genderChartCanvas = ref(null);
const graduationYearChartCanvas = ref(null);
const educationProfileChartCanvas = ref(null);
const bsitReasonsChartCanvas = ref(null);
const graduateDegreesChartCanvas = ref(null);
const graduateReasonsChartCanvas = ref(null);
const timeToJobChartCanvas = ref(null);
const jobDifficultiesChartCanvas = ref(null);
const startingSalaryChartCanvas = ref(null);

onMounted(() => {
  // Create all charts
  createGenderChart();
  createGraduationYearChart();
  createEducationProfileChart();
  createBSITReasonsChart();
  createGraduateDegreesChart();
  createGraduateReasonsChart();
  createTimeToJobChart();
  createJobDifficultiesChart();
  createStartingSalaryChart();
});
const applyFilters = () => {
  router.get(route('admin.dashboard'), {
    gender: filters.gender,
    salary_range: filters.salary_range
  }, {
    preserveState: true,
    replace: true
  });
};
const filters = reactive({
  gender: props.filters.gender || '',
  salary_range: props.filters.salary_range || ''
});

const resetFilters = () => {
  filters.gender = '';
  filters.salary_range = '';
  applyFilters();
};
const formatSalaryRange = (range) => {
  const ranges = {
    'below_10k': 'Below ₱10k',
    '10k_15k': '₱10k-₱15k',
    '15k_20k': '₱15k-₱20k',
    '20k_30k': '₱20k-₱30k',
    'above_30k': 'Above ₱30k'
  };
  return ranges[range] || range;
};
const createGenderChart = () => {
  if (genderChartCanvas.value && props.demographics?.gender) {
    new Chart(genderChartCanvas.value.getContext('2d'), {
      type: 'pie',
      data: {
        labels: props.demographics.gender.labels,
        datasets: [{
          data: props.demographics.gender.data,
          backgroundColor: props.demographics.gender.colors,
          borderWidth: 0
        }]
      },
      options: getChartOptions('Gender Distribution')
    });
  }
};

const createGraduationYearChart = () => {
  if (graduationYearChartCanvas.value && props.demographics?.graduationYears) {
    new Chart(graduationYearChartCanvas.value.getContext('2d'), {
      type: 'bar',
      data: {
        labels: props.demographics.graduationYears.labels,
        datasets: [{
          label: 'Alumni Count',
          data: props.demographics.graduationYears.data,
          backgroundColor: props.demographics.graduationYears.color,
          borderWidth: 0
        }]
      },
      options: getChartOptions('Graduation Year Distribution', true)
    });
  }
};

const createEducationProfileChart = () => {
  if (educationProfileChartCanvas.value && props.demographics?.educationalProfiles) {
    new Chart(educationProfileChartCanvas.value.getContext('2d'), {
      type: 'bar', // Changed from 'horizontalBar'
      data: {
        labels: props.demographics.educationalProfiles.labels,
        datasets: [{
          label: 'Alumni Count',
          data: props.demographics.educationalProfiles.data,
          backgroundColor: props.demographics.educationalProfiles.colors,
          borderWidth: 0
        }]
      },
      options: {
        ...getChartOptions('Primary Degrees Earned', true),
        indexAxis: 'y' // Add this to make it horizontal
      }
    });
  }
};


const createBSITReasonsChart = () => {
  if (bsitReasonsChartCanvas.value && props.demographics?.bsitReasons) {
    new Chart(bsitReasonsChartCanvas.value.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: props.demographics.bsitReasons.labels,
        datasets: [{
          data: props.demographics.bsitReasons.data,
          backgroundColor: props.demographics.bsitReasons.colors,
          borderWidth: 0
        }]
      },
      options: getChartOptions('Reasons for Taking BSIT')
    });
  }
};

const createGraduateDegreesChart = () => {
  if (graduateDegreesChartCanvas.value && props.demographics?.graduateStudies?.degrees) {
    new Chart(graduateDegreesChartCanvas.value.getContext('2d'), {
      type: 'bar',
      data: {
        labels: props.demographics.graduateStudies.degrees.labels,
        datasets: [{
          label: 'Alumni Count',
          data: props.demographics.graduateStudies.degrees.data,
          backgroundColor: props.demographics.graduateStudies.degrees.color,
          borderWidth: 0
        }]
      },
      options: getChartOptions('Graduate Degrees Pursued', true)
    });
  }
};

const createGraduateReasonsChart = () => {
  if (graduateReasonsChartCanvas.value && props.demographics?.graduateStudies?.reasons) {
    new Chart(graduateReasonsChartCanvas.value.getContext('2d'), {
      type: 'pie',
      data: {
        labels: props.demographics.graduateStudies.reasons.labels,
        datasets: [{
          data: props.demographics.graduateStudies.reasons.data,
          backgroundColor: props.demographics.graduateStudies.reasons.colors,
          borderWidth: 0
        }]
      },
      options: getChartOptions('Reasons for Graduate Studies')
    });
  }
};

const createTimeToJobChart = () => {
  if (timeToJobChartCanvas.value && props.demographics?.jobHunting?.timeToJob) {
    new Chart(timeToJobChartCanvas.value.getContext('2d'), {
      type: 'bar',
      data: {
        labels: props.demographics.jobHunting.timeToJob.labels,
        datasets: [{
          label: 'Alumni Count',
          data: props.demographics.jobHunting.timeToJob.data,
          backgroundColor: props.demographics.jobHunting.timeToJob.color,
          borderWidth: 0
        }]
      },
      options: getChartOptions('Time to First Job', true)
    });
  }
};

const createJobDifficultiesChart = () => {
  if (jobDifficultiesChartCanvas.value && props.demographics?.jobHunting?.difficulties) {
    new Chart(jobDifficultiesChartCanvas.value.getContext('2d'), {
      type: 'bar', // Changed from 'horizontalBar'
      data: {
        labels: props.demographics.jobHunting.difficulties.labels,
        datasets: [{
          label: 'Number of Reports',
          data: props.demographics.jobHunting.difficulties.data,
          backgroundColor: props.demographics.jobHunting.difficulties.colors,
          borderWidth: 0
        }]
      },
      options: {
        ...getChartOptions('Job Hunting Difficulties', true),
        indexAxis: 'y' // Add this to make it horizontal
      }
    });
  }
};

const createStartingSalaryChart = () => {
  if (startingSalaryChartCanvas.value && props.demographics?.startingSalaries) {
    new Chart(startingSalaryChartCanvas.value.getContext('2d'), {
      type: 'bar',
      data: {
        labels: props.demographics.startingSalaries.labels,
        datasets: [{
          label: 'Alumni Count',
          data: props.demographics.startingSalaries.data,
          backgroundColor: props.demographics.startingSalaries.color,
          borderWidth: 0
        }]
      },
      options: getChartOptions('Starting Salaries', true)
    });
  }
};

const getChartOptions = (title = '', showLegend = false) => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    title: {
      display: false,
      text: title,
      color: '#ffffff',
      font: {
        size: 16
      }
    },
    legend: {
      display: showLegend,
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
      bodyColor: '#ffffff',
      callbacks: {
        label: function(context) {
          let label = context.label || '';
          if (label) {
            label += ': ';
          }
          label += context.formattedValue;
          return label;
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: false,
        color: 'rgba(255, 255, 255, 0.1)'
      },
      ticks: {
        color: '#ffffff'
      }
    },
    y: {
      grid: {
        color: 'rgba(255, 255, 255, 0.1)'
      },
      ticks: {
        color: '#ffffff'
      }
    }
  }
});
</script>
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
    .no-data-message {
  text-align: center;
  padding: 20px;
  color: var(--text-secondary);
}
/* Your existing styles remain unchanged */
.chart-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.chart-container {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 10px;
  padding: 15px;
}

.chart-wrapper {
  position: relative;
  height: 300px;
  width: 100%;
}

.additional-stat {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 10px;
  padding: 15px;
  text-align: center;
  margin-top: 15px;
}

.additional-stat .stat-value {
  font-size: 24px;
  font-weight: 700;
  color: var(--primary);
}

.additional-stat .stat-title {
  font-size: 14px;
  color: var(--text-secondary);
}

@media (min-width: 768px) {
  .chart-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
.filter-controls {
  background: #2d3748;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group label {
  color: #e2e8f0;
  font-weight: 500;
}

.filter-group select, .filter-group input {
  background: #4a5568;
  border: 1px solid #4a5568;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.section-actions {
  display: flex;
  gap: 0.5rem;
}

.toggle-btn, .export-btn {
  background: #4a5568;
  color: white;
  border: none;
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.875rem;
}

.toggle-btn:hover, .export-btn:hover {
  background: #2d3748;
}

.chart-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.chart-actions button {
  background: #4a5568;
  color: white;
  border: none;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.75rem;
}

.chart-actions button:hover {
  background: #2d3748;
}
.filters-section {
  background: #2d3748;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
}

.filter-controls {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.filter-group label {
  font-size: 0.875rem;
  color: #a0aec0;
}

.filter-group select {
  background: #1a202c;
  color: white;
  border: 1px solid #4a5568;
  border-radius: 0.25rem;
  padding: 0.5rem;
  min-width: 180px;
}

.reset-btn {
  background: #4a5568;
  color: white;
  border: none;
  border-radius: 0.25rem;
  padding: 0.5rem 1rem;
  cursor: pointer;
  align-self: flex-end;
  margin-left: auto;
}

.reset-btn:hover {
  background: #2d3748;
}

.filter-note {
  font-size: 0.875rem;
  color: #a0aec0;
  margin-top: 0.5rem;
}
</style>