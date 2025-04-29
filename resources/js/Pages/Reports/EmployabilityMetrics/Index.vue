<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';

const props = defineProps({
    metrics: Object,
    filters: Object
});

const filters = ref(props.filters);
const isFiltering = ref(false);
let filterTimeout = null;

const applyFilters = () => {
    isFiltering.value = true;
    clearTimeout(filterTimeout);
    
    filterTimeout = setTimeout(() => {
        router.get(route('reports.employability-metrics.index'), filters.value, {
            preserveState: true,
            replace: true,
            onFinish: () => isFiltering.value = false
        });
    }, 300);
};

watch(filters, applyFilters, { deep: true });

const downloadReport = (format) => {
    window.location.href = route('reports.employability-metrics.export', {
        ...filters.value,
        format: format
    });
};
</script>
<template>
    <AdminLayout title="Employability Metrics">
        <template #header>
            <h2 class="page-title">Employability Metrics Report</h2>
        </template>

        <div class="report-container">
            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>From Year</label>
                        <input
                            v-model="filters.year_from"
                            type="number"
                            placeholder="Start year"
                            class="filter-input"
                        />
                    </div>
                    <div class="filter-group">
                        <label>To Year</label>
                        <input
                            v-model="filters.year_to"
                            type="number"
                            placeholder="End year"
                            class="filter-input"
                        />
                    </div>
                    <div class="filter-group">
                        <label>Degree Program</label>
                        <input
                            v-model="filters.degree_program"
                            type="text"
                            placeholder="Filter by degree"
                            class="filter-input"
                        />
                    </div>
                    <div class="filter-group">
                        <label>Campus</label>
                        <input
                            v-model="filters.campus"
                            type="text"
                            placeholder="Filter by campus"
                            class="filter-input"
                        />
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button
                        @click="downloadReport('excel')"
                        class="export-button excel-button"
                    >
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button
                        @click="downloadReport('pdf')"
                        class="export-button pdf-button"
                    >
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div v-if="isFiltering" class="filter-loading">
                <div class="loading-spinner"></div>
                <span>Applying filters...</span>
            </div>

            <!-- Employability Index Report -->
            <div class="section">
                <h3 class="section-title">Employability Index</h3>
                <div class="metric-card">
                    <div class="metric-value">
                        {{ metrics.indexReport?.avg_score?.toFixed(2) || 0 }}
                    </div>
                    <div class="metric-label">Average Score</div>
                    <div class="metric-subtext">
                        Based on {{ metrics.indexReport?.count || 0 }} graduates
                    </div>
                </div>
            </div>

            <!-- Department Performance -->
            <div class="section">
                <h3 class="section-title">Department Performance</h3>
                <div class="chart-container">
                    <BarChart
                        :labels="metrics.departmentPerformance?.map(d => d.degree_earned) || []"
                        :data="metrics.departmentPerformance?.map(d => d.avg_score) || []"
                        label="Average Employability Score"
                    />
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Degree Program</th>
                                <th>Average Score</th>
                                <th>Graduate Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dept in metrics.departmentPerformance" :key="dept.degree_earned">
                                <td>{{ dept.degree_earned }}</td>
                                <td>{{ dept.avg_score.toFixed(2) }}</td>
                                <td>{{ dept.count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Trend Analysis -->
            <div class="section">
                <h3 class="section-title">Trend Analysis</h3>
                <div class="chart-container">
                    <LineChart
                        :labels="metrics.trendAnalysis?.map(t => t.year_graduated) || []"
                        :data="metrics.trendAnalysis?.map(t => t.avg_score) || []"
                        label="Average Score Over Time"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Add your existing styles here */
.report-container {
    padding: 20px;
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
    margin-bottom: 15px;
}

.section {
    margin-bottom: 30px;
    background: var(--card-bg);
    padding: 20px;
    border-radius: 8px;
}

.section-title {
    color: var(--primary);
    margin-bottom: 15px;
}

.metric-card {
    text-align: center;
    padding: 20px;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
}

.metric-value {
    font-size: 36px;
    font-weight: bold;
    color: var(--primary);
}

.metric-label {
    font-size: 16px;
    color: var(--text-secondary);
}

.table-responsive {
    margin-top: 20px;
    overflow-x: auto;
}
.filter-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 15px;
}

.export-button {
    padding: 8px 15px;
    border-radius: 4px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.excel-button {
    background: #2ecc71;
    color: white;
}

.excel-button:hover {
    background: #27ae60;
    transform: translateY(-1px);
}

.pdf-button {
    background: #e74c3c;
    color: white;
}

.pdf-button:hover {
    background: #c0392b;
    transform: translateY(-1px);
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
/* Add more styles as needed */
</style>