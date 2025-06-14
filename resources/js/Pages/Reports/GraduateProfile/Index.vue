<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    initialData: {
        type: Object,
        default: () => ({
            graduates: { data: [], total: 0 },
            statistics: {
                totalGraduates: 0,
                yearlyGraduation: [],
                degreesDistribution: []
            }
        })
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const graduates = ref(props.initialData.graduates);
const statistics = ref(props.initialData.statistics);
const filters = ref(props.filters);
const showFileViewer = ref(false);
const currentFile = ref(null);
const isFiltering = ref(false);
let filterTimeout = null;
const printFilters = ref({ ...props.filters });
const filteredGraduates = ref([]);
// Safe computed properties
const totalCount = computed(() => graduates.value?.total || 0);
const latestGradYear = computed(() => Math.max(...(statistics.value.yearlyGraduation?.map(item => item.year_graduated) || [0])));
const earliestGradYear = computed(() => Math.min(...(statistics.value.yearlyGraduation?.map(item => item.year_graduated) || [0])));
const filteredCount = computed(() => filteredGraduates.value.length);
const printCount = computed(() => {
  return filteredGraduates.value.filter(g => willPrint(g)).length;
});

const generateReport = () => {
    isFiltering.value = true;
    clearTimeout(filterTimeout);
    
    filterTimeout = setTimeout(() => {
        const cleanedFilters = Object.fromEntries(
            Object.entries(filters.value).filter(([_, value]) => value !== null && value !== '')
        );
        
        router.get(route('reports.graduate-profiles.index'), cleanedFilters, {
            preserveState: true,
            replace: true,
            onFinish: () => isFiltering.value = false
        });
    }, 300);
};

watch(filters, generateReport, { deep: true });

const viewFile = (file) => {
    currentFile.value = file;
    showFileViewer.value = true;
};

const closeFileViewer = () => {
    showFileViewer.value = false;
    currentFile.value = null;
};

const willPrint = (graduate) => {
  return 
    (!printFilters.value.search || 
     `${graduate.user?.first_name} ${graduate.user?.last_name}`.includes(printFilters.value.search) &&
    (!printFilters.value.graduationYearFrom || 
     graduate.year_graduated >= printFilters.value.graduationYearFrom) &&
    (!printFilters.value.graduationYearTo || 
     graduate.year_graduated <= printFilters.value.graduationYearTo) &&
    (!printFilters.value.degree || 
     graduate.degree_earned.includes(printFilters.value.degree))
  );
};

// Update print filters when export is clicked
const updatePrintFilters = () => {
  printFilters.value = { ...filters.value };
};

const downloadReport = (format) => {
  updatePrintFilters();
  window.location.href = route('reports.graduate-profiles.export', {
    ...printFilters.value,
    format: format
  });
};
</script>

<template>
    <AdminLayout title="Graduate Profile Report">
        <template #header>
        </template>

        <div class="report-container">
            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by name or email"
                            class="filter-input"
                        />
                    </div>
                    
                    <div class="filter-group">
                        <label>From Year</label>
                        <input
                            v-model="filters.graduationYearFrom"
                            type="number"
                            min="1900"
                            :max="new Date().getFullYear()"
                            placeholder="Start year"
                            class="filter-input"
                        />
                    </div>
                    
                    <div class="filter-group">
                        <label>To Year</label>
                        <input
                            v-model="filters.graduationYearTo"
                            type="number"
                            min="1900"
                            :max="new Date().getFullYear()"
                            placeholder="End year"
                            class="filter-input"
                        />
                    </div>
                    
                    <div class="filter-group">
                        <label>Degree</label>
                        <input
                            v-model="filters.degree"
                            type="text"
                            placeholder="Filter by degree"
                            class="filter-input"
                        />
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button
                        @click="downloadReport('pdf')"
                        class="export-button pdf-button"
                    >
                        <span class="file-icon pdf-icon"></span>
                        Export PDF
                    </button>
                    <button
                        @click="downloadReport('excel')"
                        class="export-button excel-button"
                    >
                        <span class="file-icon excel-icon"></span>
                        Export Excel
                    </button>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div v-if="isFiltering" class="filter-loading">
                <div class="loading-spinner"></div>
                <span>Applying filters...</span>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users-icon"></div>
                    <div class="stat-content">
                        <h3>Total Graduates</h3>
                        <p>{{ statistics.totalGraduates }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon calendar-icon"></div>
                    <div class="stat-content">
                        <h3>Latest Graduation Year</h3>
                        <p>{{ latestGradYear }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon calendar-icon"></div>
                    <div class="stat-content">
                        <h3>Earliest Graduation Year</h3>
                        <p>{{ earliestGradYear }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon degree-icon"></div>
                    <div class="stat-content">
                        <h3>Unique Degrees</h3>
                        <p>{{ statistics.degreesDistribution?.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter Status -->
            <div class="filter-status">
                <span v-if="isFiltering" class="loading-indicator">Applying filters...</span>
                <span v-else class="result-count">
                    Showing {{ filteredCount }} of {{ totalCount }} records
                </span>
            </div>

            <!-- Graduates Table -->
            <div class="data-table-container">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Degree</th>
              <th>Year</th>
              <th>Campus</th>
              <th class="print-indicator">Included in Print</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="filteredGraduates.length > 0">
              <tr 
                v-for="graduate in filteredGraduates" 
                :key="graduate.id"
                :class="{ 'will-print': willPrint(graduate) }"
              >
                <td>{{ graduate.user?.first_name }} {{ graduate.user?.last_name }}</td>
                <td>{{ graduate.degree_earned }}</td>
                <td>{{ graduate.year_graduated }}</td>
                <td>{{ graduate.campus || 'N/A' }}</td>
                <td class="print-indicator">
                  <span v-if="willPrint(graduate)" class="print-included">✓ Included</span>
                  <span v-else class="print-excluded">✗ Excluded</span>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="5" class="no-results">No records match your filters</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="print-preview-summary">
        <h3>Print Preview Summary</h3>
        <div class="summary-stats">
          <div class="summary-stat">
            <span class="stat-label">Total Records:</span>
            <span class="stat-value">{{ totalCount }}</span>
          </div>
          <div class="summary-stat">
            <span class="stat-label">Filtered Records:</span>
            <span class="stat-value">{{ filteredCount }}</span>
          </div>
          <div class="summary-stat">
            <span class="stat-label">Will Print:</span>
            <span class="stat-value">{{ printCount }}</span>
          </div>
        </div>
      </div>
    </div>
        </div>

        <!-- File Viewer Modal -->
        <div v-if="showFileViewer" class="file-viewer-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Document Viewer</h3>
                    <button @click="closeFileViewer" class="close-button">&times;</button>
                </div>
                <div class="modal-body">
                    <iframe 
                        v-if="currentFile"
                        :src="currentFile"
                        class="document-viewer"
                    ></iframe>
                </div>
                <div class="modal-footer">
                    <button @click="closeFileViewer" class="modal-button">Close</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

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

.report-container {
    padding: 20px;
    color: var(--text-primary);
}

.page-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.filter-bar {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    margin-bottom: 8px;
    font-size: 14px;
    color: var(--text-secondary);
}

.filter-input {
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    color: var(--text-primary);
    transition: all 0.3s;
}

.filter-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(255, 140, 0, 0.2);
}

.filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.export-button {
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.pdf-button {
    background: #e74c3c;
    color: white;
}

.excel-button {
    background: #2ecc71;
    color: white;
}

.export-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.file-icon {
    display: inline-block;
    width: 16px;
    height: 16px;
    background-size: contain;
    background-repeat: no-repeat;
}

.pdf-icon {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512'%3E%3Cpath fill='white' d='M181.9 256.1c-5-16-4.9-46.9-2-46.9 8.4 0 7.6 36.9 2 46.9zm-1.7 47.2c-7.7 20.2-17.3 43.3-28.4 62.7 18.3-7 39-17.2 62.9-21.9-12.7-9.6-24.9-23.4-34.5-40.8zM86.1 428.1c0 .8 13.2-5.4 34.9-40.2-6.7 6.3-29.1 24.5-34.9 40.2zM248 160h136v328c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V24C0 10.7 10.7 0 24 0h200v136c0 13.3 10.7 24 24 24zm-8 171.8c-20-12.2-33.3-29-42.7-53.8 4.5-18.5 11.6-46.6 6.2-64.2-4.7-29.4-42.4-26.5-47.8-6.8-5 18.3-.4 44.1 8.1 77-11.5 27.6-28.7 64.6-40.8 85.8-.1 0-.1.1-.2.1-27.1 13.9-73.6 44.5-73.6 44.5v-180c37.5-31.5 87.1-50.7 138.2-50.7 7.2 0 15.4.1 23.5.6 4.1-8.2 7.6-16.3 11-24.4H248V159.5h24V160h24v.5h24V160h24v160.3z'%3E%3C/path%3E%3C/svg%3E");
}

.excel-icon {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512'%3E%3Cpath fill='white' d='M14 3.2C7.4 10.4 4 20.4 4 32v448c0 11.6 3.4 21.6 10 28.8l7-7c-4.5-5.7-7-12.8-7-21.8V32c0-9 2.5-16.1 7-21.8l-7-7zm356 7l-7 7c4.5 5.7 7 12.8 7 21.8v448c0 9-2.5 16.1-7 21.8l7 7c6.6-7.2 10-17.2 10-28.8V32c0-11.6-3.4-21.6-10-28.8zM120 128c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h144v-32H120zm0 64c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h112v-32H120zm0 64c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h80v-32h-80z'%3E%3C/path%3E%3C/svg%3E");
}

.filter-loading {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary);
    margin-bottom: 20px;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 140, 0, 0.3);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
}

.users-icon {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'%3E%3Cpath fill='%23ff8c00' d='M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312V248H440v64h64zm0 72V320H440v64h64zM592 312V248H528v64h64zm0 72V320H528v64h64z'%3E%3C/path%3E%3C/svg%3E");
}

.calendar-icon {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23ff8c00' d='M152 24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H64C28.7 64 0 92.7 0 128v16 48V448c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V192 144 128c0-35.3-28.7-64-64-64H344V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H152V24zM48 192H400V448c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192z'%3E%3C/path%3E%3C/svg%3E");
}

.degree-icon {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'%3E%3Cpath fill='%23ff8c00' d='M320 32c-8.1 0-16.1 1.4-23.7 4.1L15.8 137.4C6.3 140.9 0 149.9 0 160s6.3 19.1 15.8 22.6l57.9 20.9C57.3 229.3 48 259.8 48 291.9v28.1c0 28.4-10.8 57.7-22.3 80.8c-6.5 13-13.9 25.8-22.5 37.6C0 442.7-.9 448.3 .9 453.4s6 8.9 11.2 10.2l64 16c4.2 1.1 8.7 .3 12.4-2s6.3-6.1 7.1-10.4c8.6-42.8 4.3-81.2-2.1-108.7C90.3 344.3 86 329.8 80 316.5V291.9c0-30.2 10.2-58.7 27.9-81.5c12.9-15.5 29.6-28 49.2-35.7l157-61.7c8.2-3.2 17.5 .8 20.7 9s-.8 17.5-9 20.7l-157 61.7c-12.4 4.9-23.3 12.4-32.2 21.6l159.6 57.6c7.6 2.7 15.6 4.1 23.7 4.1s16.1-1.4 23.7-4.1L624.2 182.6c9.5-3.4 15.8-12.5 15.8-22.6s-6.3-19.1-15.8-22.6L343.7 36.1C336.1 33.4 328.1 32 320 32zM128 408c0 35.3 86 72 192 72s192-36.7 192-72L496.7 262.6 354.5 314c-11.1 4-22.8 6-34.5 6s-23.5-2-34.5-6L143.3 262.6 128 408z'%3E%3C/path%3E%3C/svg%3E");
}

.stat-content h3 {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.stat-content p {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
}

.data-table {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    overflow: hidden;
}

.data-table table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th, .data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--card-border);
}

.data-table th {
    background: rgba(255, 255, 255, 0.05);
    font-weight: 600;
    color: var(--text-primary);
}

.data-table tr:hover {
    background: rgba(255, 255, 255, 0.03);
}

.view-file-button {
    background: var(--primary);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.view-file-button:hover {
    background: #e67e00;
}

/* File Viewer Modal */
.file-viewer-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: var(--bg-dark);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid var(--card-border);
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
    color: var(--text-secondary);
    font-size: 24px;
    cursor: pointer;
    padding: 0 10px;
}

.close-button:hover {
    color: var(--text-primary);
}

.modal-body {
    flex: 1;
    padding: 0;
    overflow: hidden;
}

.document-viewer {
    width: 100%;
    height: 70vh;
    border: none;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid var(--card-border);
    display: flex;
    justify-content: flex-end;
}

.modal-button {
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.modal-button:hover {
    background: #e67e00;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .data-table table {
        display: block;
        overflow-x: auto;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .export-button {
        width: 100%;
        justify-content: center;
    }
}
.filter-bar {
    background: var(--card-bg);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.filter-input {
    width: 100%;
    padding: 8px 12px;
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--card-border);
    border-radius: 4px;
    color: var(--text-primary);
}

.filter-status {
    margin-top: 10px;
    font-size: 14px;
    color: var(--text-secondary);
}

.loading-indicator {
    color: var(--primary);
}

.result-count {
    font-weight: 500;
}

.data-table {
    overflow-x: auto;
}

.no-results {
    text-align: center;
    padding: 20px;
    color: var(--text-secondary);
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