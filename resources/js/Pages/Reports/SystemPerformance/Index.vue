<template>
  <AdminLayout title="System Performance Reports">
    <div class="header">
      <div class="action-buttons">
        <button @click="downloadReport('pdf')" class="export-btn pdf">Export PDF</button>
        <button @click="downloadReport('excel')" class="export-btn excel">Export Excel</button>
      </div>
    </div>
    <div class="report-container">
      <!-- Filter Section -->
      <div class="filter-section">
        <div class="filter-group">
          <label>From Date</label>
          <input v-model="filters.date_from" type="date" />
        </div>
        <div class="filter-group">
          <label>To Date</label>
          <input v-model="filters.date_to" type="date" />
        </div>
        <div class="filter-group">
          <label>Content Type</label>
          <select v-model="filters.content_type">
            <option value="">All Types</option>
            <option v-for="type in metrics.contentTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>
        <button @click="resetFilters" class="reset-btn">Reset Filters</button>
      </div>
      <!-- Loading Indicator -->
      <div v-if="isLoading" class="loading-indicator">
        <div class="spinner"></div>
        <span>Loading performance data...</span>
      </div>
      <!-- Tab Navigation -->
      <div class="tabs">
        <button @click="activeTab = 'engagement'" :class="{ active: activeTab === 'engagement' }">User Engagement</button>
        <button @click="activeTab = 'tam'" :class="{ active: activeTab === 'tam' }">TAM Evaluation</button>
      </div>
      <!-- User Engagement Report -->
      <div v-if="activeTab === 'engagement'" class="report-section">
        <div class="section-header">
          <h2>User Engagement Metrics</h2>
          <p>Analysis of user activity and interactions</p>
        </div>
        <div class="metrics-grid">
          <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-users"></i></div>
            <div class="metric-content">
              <h3>Active Users</h3>
              <p class="metric-value">{{ metrics.activeUsers.active_users }}</p>
              <p class="metric-description">out of {{ metrics.activeUsers.total_users }} total users</p>
            </div>
          </div>
          <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-comments"></i></div>
            <div class="metric-content">
              <h3>Messages Sent</h3>
              <p class="metric-value">{{ metrics.messagingActivity.total_messages }}</p>
              <p class="metric-description">by {{ metrics.messagingActivity.active_messengers }} users</p>
            </div>
          </div>
        </div>
        <!-- Content Interactions Table -->
        <div class="card">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Content Type</th>
                  <th>Items Created</th>
                  <th>Reactions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="metrics.contentInteractions.length === 0">
                  <td colspan="3" class="no-data">No content interaction data found</td>
                </tr>
                <tr v-for="(content, index) in metrics.contentInteractions" :key="index">
                  <td>{{ content.type }}</td>
                  <td>{{ content.content_count }}</td>
                  <td>{{ content.reaction_count }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- TAM Evaluation Report -->
      <div v-if="activeTab === 'tam'" class="report-section">
        <div class="section-header">
          <h2>Technology Acceptance Model (TAM) Evaluation</h2>
          <p>User feedback and system adoption metrics</p>
        </div>
        <div class="metrics-grid">
          <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-mouse-pointer"></i></div>
            <div class="metric-content">
              <h3>Ease of Use</h3>
              <p class="metric-value">{{ metrics.tamEvaluation.ease_of_use.toFixed(1) }}/5</p>
              <div class="rating-bar">
                <div class="rating-fill" :style="{ width: (metrics.tamEvaluation.ease_of_use / 5 * 100) + '%' }"></div>
              </div>
            </div>
          </div>
          <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
            <div class="metric-content">
              <h3>Usefulness</h3>
              <p class="metric-value">{{ metrics.tamEvaluation.usefulness.toFixed(1) }}/5</p>
              <div class="rating-bar">
                <div class="rating-fill" :style="{ width: (metrics.tamEvaluation.usefulness / 5 * 100) + '%' }"></div>
              </div>
            </div>
          </div>
          <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-smile"></i></div>
            <div class="metric-content">
              <h3>Satisfaction</h3>
              <p class="metric-value">{{ metrics.tamEvaluation.satisfaction.toFixed(1) }}/5</p>
              <div class="rating-bar">
                <div class="rating-fill" :style="{ width: (metrics.tamEvaluation.satisfaction / 5 * 100) + '%' }"></div>
              </div>
            </div>
          </div>
          <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-redo"></i></div>
            <div class="metric-content">
              <h3>Intention to Use</h3>
              <p class="metric-value">{{ metrics.tamEvaluation.intention_to_use.toFixed(1) }}/5</p>
              <div class="rating-bar">
                <div class="rating-fill" :style="{ width: (metrics.tamEvaluation.intention_to_use / 5 * 100) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
        <!-- Feature Usage Table -->
        <div class="card">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Feature</th>
                  <th>Usage Count</th>
                  <th>Popularity</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="metrics.featureUsage.length === 0">
                  <td colspan="3" class="no-data">No feature usage data found</td>
                </tr>
                <tr v-for="(feature, index) in metrics.featureUsage" :key="index">
                  <td>{{ feature.type }}</td>
                  <td>{{ feature.count }}</td>
                  <td>
                    <div class="popularity-bar">
                      <div class="popularity-fill" :style="{ width: (feature.count / maxFeatureUsage * 100) + '%' }"></div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  metrics: Object,
  filters: Object,
});

const filters = ref(props.filters);
const isLoading = ref(false);
const activeTab = ref('engagement');

const maxFeatureUsage = computed(() => {
  return Math.max(...props.metrics.featureUsage.map(item => item.count));
});

const generateReport = debounce(() => {
  isLoading.value = true;
  router.get(route('reports.system-performance.index'), filters.value, {
    preserveState: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    },
  });
}, 500);

watch(filters, generateReport, { deep: true });

const downloadReport = (format) => {
  window.location.href = route('reports.system-performance.export', {
    ...filters.value,
    format: format,
  });
};

const resetFilters = () => {
  filters.value = {};
};
</script>
  
  <style scoped>
  /* Base Styles */
  :root {
    --primary: #ff8c00;
    --primary-light: rgba(255, 140, 0, 0.1);
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
    --bg-dark: #1a1a1a;
    --bg-darker: #121212;
    --card-bg: rgba(40, 40, 40, 0.7);
    --card-border: rgba(255, 255, 255, 0.1);
  }
  
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    color: var(--text-primary);
  }
  
  .header h1 {
    font-size: 1.5rem;
    margin: 0;
  }
  
  .action-buttons {
    display: flex;
    gap: 0.75rem;
  }
  
  .export-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    color: white;
  }
  
  .export-btn.pdf {
    background-color: #F44336;
  }
  
  .export-btn.excel {
    background-color: #4CAF50;
  }
  
  .report-container {
    padding: 1.5rem;
    color: var(--text-primary);
  }
  
  /* Filter Section */
  .filter-section {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: var(--card-bg);
    border-radius: 8px;
    border: 1px solid var(--card-border);
  }
  
  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .filter-group label {
    font-size: 0.875rem;
    color: var(--text-secondary);
  }
  
  .filter-group input,
  .filter-group select {
    padding: 0.5rem;
    border-radius: 4px;
    border: 1px solid var(--card-border);
    color: var(--text-primary);
  }
  
  .reset-btn {
    grid-column: 1 / -1;
    padding: 0.5rem;
    background-color: transparent;
    color: var(--primary);
    border: 1px solid var(--primary);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .reset-btn:hover {
    background-color: var(--primary-light);
  }
  
  /* Loading Indicator */
  .loading-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: var(--card-bg);
    border-radius: 4px;
  }
  
  .spinner {
    width: 1.5rem;
    height: 1.5rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s ease-in-out infinite;
  }
  
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
  
  /* Tabs */
  .tabs {
    display: flex;
    border-bottom: 1px solid var(--card-border);
    margin-bottom: 1.5rem;
  }
  
  .tabs button {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--text-secondary);
    cursor: pointer;
    font-weight: 500;
  }
  
  .tabs button.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
  }
  
  /* Report Sections */
  .report-section {
    margin-bottom: 2rem;
  }
  
  .section-header {
    margin-bottom: 1rem;
  }
  
  .section-header h2 {
    margin: 0 0 0.25rem 0;
    color: var(--primary);
  }
  
  .section-header p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
  }
  
  /* Metrics Grid */
  .metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  
  .metric-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background-color: var(--card-bg);
    border-radius: 8px;
    border: 1px solid var(--card-border);
  }
  
  .metric-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background-color: var(--primary-light);
    color: var(--primary);
    font-size: 1.25rem;
  }
  
  .metric-content {
    flex: 1;
  }
  
  .metric-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: var(--text-primary);
  }
  
  .metric-value {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
  }
  
  .metric-description {
    margin: 0.25rem 0 0 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
  }
  
  /* Rating Bars */
  .rating-bar {
    width: 100%;
    height: 8px;
    background-color: var(--bg-darker);
    border-radius: 4px;
    margin-top: 0.5rem;
    overflow: hidden;
  }
  
  .rating-fill {
    height: 100%;
    background-color: var(--primary);
    border-radius: 4px;
  }
  
  /* Popularity Bars */
  .popularity-bar {
    width: 100%;
    height: 8px;
    background-color: var(--bg-darker);
    border-radius: 4px;
    overflow: hidden;
  }
  
  .popularity-fill {
    height: 100%;
    background-color: var(--primary);
    border-radius: 4px;
  }
  
  /* Card */
  .card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s;
  }
  
  .card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transform: translateY(-2px);
  }
  
  /* Tables */
  .table-container {
    overflow-x: auto;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  th, td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--card-border);
  }
  
  th {
    background-color: var(--bg-darker);
    color: var(--text-primary);
    font-weight: 600;
  }
  
  tr:hover {
    background-color: var(--primary-light);
  }
  
  .no-data {
    text-align: center;
    padding: 1.5rem;
    color: var(--text-secondary);
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .filter-section {
      grid-template-columns: 1fr;
    }
    
    .header {
      flex-direction: column;
      gap: 1rem;
      align-items: flex-start;
    }
    
    .metrics-grid {
      grid-template-columns: 1fr;
    }
    
    .tabs {
      overflow-x: auto;
      white-space: nowrap;
    }
  }
  option {
      background-color: var(--bg-dark);
      color: var(--text-secondary);
    }

    /* Optional: style on focus */
    select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 5px var(--primary-light);
    }
  </style>