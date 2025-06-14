<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';
import DonutChart from '@/Components/Charts/DonutChart.vue';

const props = defineProps({
    employmentData: {
        type: Object,
        default: () => ({
            employmentRate: 0,
            industryDistribution: [],
            jobRelevance: [],
            firstJobTimeline: [],
            tenureAnalysis: [],
            occupationalClassification: []
        })
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const filters = ref(props.filters);
const isFiltering = ref(false);
let filterTimeout = null;

// Computed properties
const employmentRatePercentage = computed(() => {
    return Math.round(props.employmentData.employmentRate * 100);
});

const generateReport = () => {
    isFiltering.value = true;
    clearTimeout(filterTimeout);
    
    filterTimeout = setTimeout(() => {
        const cleanedFilters = Object.fromEntries(
            Object.entries(filters.value).filter(([_, value]) => value !== null && value !== '')
        );
        
        router.get(route('reports.employment-status.index'), cleanedFilters, {
            preserveState: true,
            replace: true,
            onFinish: () => isFiltering.value = false
        });
    }, 300);
};

watch(filters, generateReport, { deep: true });

const downloadReport = (format) => {
    window.location.href = route('reports.employment-status.export', {
        ...filters.value,
        format: format
    });
};
</script>

<template>
    <AdminLayout title="Employment Status Report">
        <template #header>
            <h2 class="page-title">Employment Status Report</h2>
        </template>

        <div class="report-container">
            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Graduation Year Range</label>
                        <div class="range-inputs">
                            <input
                                v-model="filters.graduationYearFrom"
                                type="number"
                                placeholder="From"
                                class="filter-input"
                            />
                            <span>to</span>
                            <input
                                v-model="filters.graduationYearTo"
                                type="number"
                                placeholder="To"
                                class="filter-input"
                            />
                        </div>
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
                    
                    <div class="filter-group">
                        <label>Industry</label>
                        <select v-model="filters.industry" class="filter-input">
                            <option value="">All Industries</option>
                            <option v-for="industry in employmentData.industryDistribution" 
                                    :value="industry.name">
                                {{ industry.name }}
                            </option>
                        </select>
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

            <!-- Current Employment Dashboard -->
            <div class="section-title">
                <h3>Current Employment Dashboard</h3>
                <div class="section-divider"></div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Employment Rate Card -->
                <div class="metric-card">
                    <div class="metric-header">
                        <h4>Employment Rate</h4>
                        <div class="metric-value">{{ employmentRatePercentage }}%</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: employmentRatePercentage + '%' }"></div>
                    </div>
                    <div class="metric-description">
                        Percentage of graduates currently employed
                    </div>
                </div>
                
                <!-- Industry Distribution -->
                <div class="chart-card">
                    <h4>Industry Distribution</h4>
                    <PieChart 
                        :labels="employmentData.industryDistribution.map(i => i.name)"
                        :data="employmentData.industryDistribution.map(i => i.count)"
                        :colors="['#FF8C00', '#FFA726', '#FFB74D', '#FFCC80', '#FFE0B2']"
                    />
                </div>
                
                <!-- Job Relevance -->
                <div class="chart-card">
                    <h4>Job Relevance to Degree</h4>
                    <DonutChart 
                        :labels="['Related to Degree', 'Not Related']"
                        :data="[employmentData.jobRelevance.related, employmentData.jobRelevance.notRelated]"
                        :colors="['#4CAF50', '#F44336']"
                    />
                </div>
            </div>

            <!-- First Job Timeline Analysis -->
            <div class="section-title">
                <h3>First Job Timeline Analysis</h3>
                <div class="section-divider"></div>
            </div>
            
            <div class="analysis-card">
                <BarChart 
                    :labels="employmentData.firstJobTimeline.map(i => i.timeRange)"
                    :data="employmentData.firstJobTimeline.map(i => i.count)"
                    label="Number of Graduates"
                    color="#FF8C00"
                />
                <div class="analysis-summary">
                    <div class="summary-item">
                        <div class="summary-value">
                            {{ employmentData.firstJobTimelineStats.medianDays }} days
                        </div>
                        <div class="summary-label">Median time to first job</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">
                            {{ employmentData.firstJobTimelineStats.employedWithin3Months }}%
                        </div>
                        <div class="summary-label">Employed within 3 months</div>
                    </div>
                </div>
            </div>

            <!-- Tenure Analysis -->
            <div class="section-title">
                <h3>Tenure Analysis</h3>
                <div class="section-divider"></div>
            </div>
            
            <div class="analysis-card">
                <BarChart 
                    :labels="employmentData.tenureAnalysis.map(i => i.tenureRange)"
                    :data="employmentData.tenureAnalysis.map(i => i.count)"
                    label="Number of Employees"
                    color="#2196F3"
                />
                <div class="analysis-summary">
                    <div class="summary-item">
                        <div class="summary-value">
                            {{ employmentData.tenureStats.averageYears }} years
                        </div>
                        <div class="summary-label">Average tenure</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">
                            {{ employmentData.tenureStats.medianYears }} years
                        </div>
                        <div class="summary-label">Median tenure</div>
                    </div>
                </div>
            </div>

            <!-- Occupational Classification Report -->
            <div class="section-title">
                <h3>Occupational Classification</h3>
                <div class="section-divider"></div>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Job Category</th>
                            <th>Count</th>
                            <th>Percentage</th>
                            <th>Average Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="category in employmentData.occupationalClassification" :key="category.name">
                            <td>{{ category.name }}</td>
                            <td>{{ category.count }}</td>
                            <td>{{ Math.round((category.count / employmentData.totalGraduates) * 100) }}%</td>
                            <td>${{ category.avgSalary.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
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
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

.range-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
}

.range-inputs span {
    color: var(--text-secondary);
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

.section-title {
    margin: 30px 0 15px;
    position: relative;
}

.section-title h3 {
    font-size: 18px;
    color: var(--primary);
    margin-bottom: 10px;
}

.section-divider {
    height: 1px;
    background: linear-gradient(90deg, var(--primary), transparent);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 20px;
}

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.metric-header h4 {
    font-size: 16px;
    color: var(--text-secondary);
    margin: 0;
}

.metric-value {
    font-size: 28px;
    font-weight: 600;
    color: var(--primary);
}

.progress-bar {
    height: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    margin-bottom: 15px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.metric-description {
    font-size: 14px;
    color: var(--text-secondary);
}

.chart-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 20px;
}

.chart-card h4 {
    font-size: 16px;
    color: var(--text-secondary);
    margin-top: 0;
    margin-bottom: 15px;
}

.analysis-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.analysis-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.summary-item {
    background: rgba(255, 255, 255, 0.05);
    padding: 15px;
    border-radius: 6px;
    text-align: center;
}

.summary-value {
    font-size: 24px;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 5px;
}

.summary-label {
    font-size: 14px;
    color: var(--text-secondary);
}

.table-responsive {
    overflow-x: auto;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    margin-bottom: 30px;
}

table {
    width: 100%;
    border-collapse: collapse;
    color: var(--text-secondary);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--card-border);
}

th {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 13px;
}

tr:hover td {
    background: rgba(255, 255, 255, 0.05);
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .analysis-summary {
        grid-template-columns: 1fr;
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