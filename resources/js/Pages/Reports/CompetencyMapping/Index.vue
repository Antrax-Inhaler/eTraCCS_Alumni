<template>
    <AdminLayout title="Competency Mapping Reports">
      <div class="header">
        <div class="action-buttons">
          <button @click="downloadReport('pdf')" class="export-btn pdf">
            Export PDF
          </button>
          <button @click="downloadReport('excel')" class="export-btn excel">
            Export Excel
          </button>
        </div>
      </div>
  
      <div class="report-container">
        <!-- Filter Section -->
        <div class="filter-section">
          <div class="filter-group">
            <label>From Year</label>
            <input v-model="filters.year_from" type="number" min="1900" :max="new Date().getFullYear()" />
          </div>
          
          <div class="filter-group">
            <label>To Year</label>
            <input v-model="filters.year_to" type="number" min="1900" :max="new Date().getFullYear()" />
          </div>
          
          <div class="filter-group">
            <label>Degree Program</label>
            <select v-model="filters.degree_program">
              <option value="">All Programs</option>
              <option v-for="program in reports.degreePrograms" :key="program" :value="program">
                {{ program }}
              </option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Industry</label>
            <select v-model="filters.industry">
              <option value="">All Industries</option>
              <option v-for="industry in reports.industries" :key="industry" :value="industry">
                {{ industry }}
              </option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Search Competencies</label>
            <input v-model="filters.competency_search" type="text" />
          </div>
          
          <button @click="resetFilters" class="reset-btn">
            Reset Filters
          </button>
        </div>
  
        <!-- Loading Indicator -->
        <div v-if="isLoading" class="loading-indicator">
          <div class="spinner"></div>
          <span>Loading data...</span>
        </div>
  
        <!-- Tab Navigation -->
        <div class="tabs">
          <button 
            @click="activeTab = 'skills'" 
            :class="{ active: activeTab === 'skills' }"
          >
            Skills Utilization
          </button>
          <button 
            @click="activeTab = 'curriculum'" 
            :class="{ active: activeTab === 'curriculum' }"
          >
            Curriculum Relevance
          </button>
        </div>
  
        <!-- Skills Utilization Report -->
        <div v-if="activeTab === 'skills'" class="report-section">
          <div class="section-header">
            <h2>Skills Utilization Report</h2>
            <p>Analysis of competencies learned vs employability scores</p>
          </div>
          
          <div class="card">
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Competencies Learned</th>
                    <th>Number of Graduates</th>
                    <th>Average Score</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="reports.skillsReport.length === 0">
                    <td colspan="3" class="no-data">No skills data found matching your criteria</td>
                  </tr>
                  <tr v-for="(skill, index) in reports.skillsReport" :key="index">
                    <td>{{ skill.competencies_learned }}</td>
                    <td>{{ skill.count }}</td>
                    <td>{{ skill.avg_score.toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
  
        <!-- Curriculum Relevance Report -->
        <div v-if="activeTab === 'curriculum'" class="report-section">
          <div class="section-header">
            <h2>Curriculum Relevance Report</h2>
            <p>Mapping between degree programs and job requirements</p>
          </div>
          
          <div class="card">
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Industry</th>
                    <th>Job Title</th>
                    <th>Required Competencies</th>
                    <th>Job Count</th>
                    <th>Avg Score</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="reports.curriculumReport.length === 0">
                    <td colspan="5" class="no-data">No curriculum relevance data found</td>
                  </tr>
                  <tr v-for="(item, index) in reports.curriculumReport" :key="index">
                    <td>{{ item.industry }}</td>
                    <td>{{ item.job_title }}</td>
                    <td>{{ item.required_competencies }}</td>
                    <td>{{ item.job_count }}</td>
                    <td>{{ item.avg_score ? item.avg_score.toFixed(2) : 'N/A' }}</td>
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
  import { ref, watch } from 'vue';
  import { router } from '@inertiajs/vue3';
  import debounce from 'lodash/debounce';
  import AdminLayout from '@/Layouts/AdminLayout.vue';
  
  const props = defineProps({
    reports: Object,
    filters: Object,
  });
  
  const filters = ref(props.filters);
  const isLoading = ref(false);
  const activeTab = ref('skills');
  
  const generateReport = debounce(() => {
    isLoading.value = true;
    router.get(route('reports.competency-mapping.index'), filters.value, {
      preserveState: true,
      replace: true,
      onFinish: () => {
        isLoading.value = false;
      }
    });
  }, 500);
  
  watch(filters, generateReport, { deep: true });
  
  const downloadReport = (format) => {
    window.location.href = route('reports.competency-mapping.export', {
      ...filters.value,
      format: format
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
    background-color: var(--bg-darker);
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
  }
  </style>