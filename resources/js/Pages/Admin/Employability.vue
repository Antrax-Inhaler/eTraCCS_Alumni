<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    alumni: Array,
    aggregates: Object,
    comparativeData: Object,
    skillsGap: Object,
    filters: Object,
    batchYears: Array,
    industries: Array
});

const activeTab = ref('overview');
const batchYear = ref(props.filters.batch_year || '');
const industry = ref(props.filters.industry || '');

const filter = () => {
    router.get(route('admin.employability-analytics'), {
        batch_year: batchYear.value,
        industry: industry.value
    }, {
        preserveState: true
    });
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#cccccc'
            }
        },
        tooltip: {
            backgroundColor: 'rgba(40, 40, 40, 0.9)',
            titleColor: '#ffffff',
            bodyColor: '#cccccc'
        }
    },
    scales: {
        x: {
            ticks: {
                color: '#cccccc'
            },
            grid: {
                color: 'rgba(255, 255, 255, 0.1)'
            }
        },
        y: {
            ticks: {
                color: '#cccccc'
            },
            grid: {
                color: 'rgba(255, 255, 255, 0.1)'
            }
        }
    }
};

const batchComparisonChartData = computed(() => {
    return {
        labels: props.comparativeData.batchComparison.map(item => item.batch),
        datasets: [
            {
                label: 'Average Employability Score',
                data: props.comparativeData.batchComparison.map(item => item.avg_score),
                backgroundColor: 'rgba(255, 140, 0, 0.7)'
            },
            {
                label: 'Employment Rate (%)',
                data: props.comparativeData.batchComparison.map(item => item.employment_rate),
                backgroundColor: 'rgba(76, 175, 80, 0.7)'
            }
        ]
    };
});

const industryComparisonChartData = computed(() => {
    return {
        labels: props.comparativeData.industryComparison.map(item => item.industry),
        datasets: [
            {
                label: 'Average Score',
                data: props.comparativeData.industryComparison.map(item => item.avg_score),
                backgroundColor: 'rgba(255, 140, 0, 0.7)'
            },
            {
                label: 'Related Field Rate (%)',
                data: props.comparativeData.industryComparison.map(item => item.related_field_rate),
                backgroundColor: 'rgba(76, 175, 80, 0.7)'
            }
        ]
    };
});

const skillsGapChartData = computed(() => {
    const skills = Object.keys(props.skillsGap).slice(0, 10);
    return {
        labels: skills,
        datasets: [
            {
                label: 'Required',
                data: skills.map(skill => props.skillsGap[skill].required),
                backgroundColor: 'rgba(255, 140, 0, 0.7)'
            },
            {
                label: 'Possessed',
                data: skills.map(skill => props.skillsGap[skill].possessed),
                backgroundColor: 'rgba(76, 175, 80, 0.7)'
            }
        ]
    };
});
</script>

<template>
    <AdminLayout>
        <Head title="Employability Analytics" />
        
        <div class="main-content">
            <h1 class="page-title">Employability Analytics</h1>

            <!-- Filters -->
            <div class="data-section">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Batch Year</label>
                        <select v-model="batchYear" @change="filter">
                            <option value="">All Batches</option>
                            <option v-for="year in batchYears" :value="year">{{ year }}</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Industry</label>
                        <select v-model="industry" @change="filter">
                            <option value="">All Industries</option>
                            <option v-for="ind in industries" :value="ind">{{ ind }}</option>
                        </select>
                    </div>
                    <button @click="filter" class="btn btn-primary">
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tab-nav">
                <button @click="activeTab = 'overview'" :class="{ active: activeTab === 'overview' }">
                    Overview
                </button>
                <button @click="activeTab = 'comparative'" :class="{ active: activeTab === 'comparative' }">
                    Comparative Analysis
                </button>
                <button @click="activeTab = 'skills'" :class="{ active: activeTab === 'skills' }">
                    Skills Gap
                </button>
            </div>

            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'" class="data-section">
                <!-- Key Metrics -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-title">Average Employability Score</div>
                        <div class="stat-value">{{ aggregates.average_score || 'N/A' }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Employment Rate</div>
                        <div class="stat-value">{{ aggregates.employment_rate || 'N/A' }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Related Field Rate</div>
                        <div class="stat-value">{{ aggregates.related_field_rate || 'N/A' }}%</div>
                    </div>
                </div>

                <!-- Alumni List -->
                <div class="section-header">
                    <h2 class="section-title">Alumni Data</h2>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Alumni</th>
                                <th>Score</th>
                                <th>Employment</th>
                                <th>Related to Degree</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in alumni" :key="user.id">
                                <td>
                                    <div class="user-cell">
                                        <img class="user-avatar" :src="user.profile_photo_url" alt="">
                                        <div>
                                            <div class="user-name">{{ user.name }}</div>
                                            <div class="user-email">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ user.employability_index?.employability_score || 'N/A' }}</td>
                                <td>
                                    <span v-if="user.employment_histories?.length > 0" class="status active">
                                        Employed
                                    </span>
                                    <span v-else class="status inactive">
                                        Not Employed
                                    </span>
                                </td>
                                <td>
                                    {{ user.employment_histories?.some(eh => eh.is_job_related_to_degree) ? 'Yes' : 'No' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Comparative Analysis Tab -->
            <div v-if="activeTab === 'comparative'" class="data-section">
                <div v-if="batchYear" class="chart-container">
                    <h3 class="chart-title">Batch Comparison</h3>
                    <div class="chart-wrapper">
                        <Bar :data="batchComparisonChartData" :options="chartOptions" />
                    </div>
                </div>

                <div v-if="industry" class="chart-container">
                    <h3 class="chart-title">Industry Comparison</h3>
                    <div class="chart-wrapper">
                        <Bar :data="industryComparisonChartData" :options="chartOptions" />
                    </div>
                </div>

                <div v-if="!batchYear && !industry" class="empty-state">
                    <p>Select a batch year or industry to see comparative analysis</p>
                </div>
            </div>

            <!-- Skills Gap Tab -->
            <div v-if="activeTab === 'skills'" class="data-section">
                <div v-if="industry">
                    <h3 class="section-title">Skills Gap Analysis for {{ industry }}</h3>
                    <div class="chart-container">
                        <div class="chart-wrapper">
                            <Bar :data="skillsGapChartData" :options="chartOptions" />
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Skill</th>
                                    <th>Required</th>
                                    <th>Possessed</th>
                                    <th>Gap</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(gap, skill) in skillsGap" :key="skill">
                                    <td>{{ skill }}</td>
                                    <td>{{ gap.required }}</td>
                                    <td>{{ gap.possessed }}</td>
                                    <td>
                                        <span :class="{
                                            'text-success': gap.gap <= 0,
                                            'text-danger': gap.gap > 0
                                        }">
                                            {{ gap.gap }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <p>Select an industry to see skills gap analysis</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Main Content */
.main-content {
    padding: 20px;
    color: var(--text-primary);
}

.page-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

/* Data Sections */
.data-section {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.section-header {
    margin-bottom: 20px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: rgba(40, 40, 40, 0.5);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.stat-title {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
}

/* Tables */
.table-responsive {
    overflow-x: auto;
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

/* User Cells */
.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name {
    font-weight: 500;
    color: var(--text-primary);
}

.user-email {
    font-size: 12px;
    color: var(--text-secondary);
}

/* Status Badges */
.status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
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

/* Tabs */
.tab-nav {
    display: flex;
    border-bottom: 1px solid var(--card-border);
    margin-bottom: 20px;
}

.tab-nav button {
    padding: 10px 20px;
    background: none;
    border: none;
    color: var(--text-secondary);
    font-weight: 500;
    cursor: pointer;
    position: relative;
}

.tab-nav button.active {
    color: var(--primary);
}

.tab-nav button.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary);
}

/* Charts */
.chart-container {
    margin-bottom: 30px;
}

.chart-title {
    font-size: 16px;
    margin-bottom: 15px;
    color: var(--text-secondary);
}

.chart-wrapper {
    height: 300px;
    position: relative;
}

/* Filter Grid */
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.filter-group select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--text-primary);
}

/* Buttons */
.btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #e67e00;
    transform: translateY(-1px);
}

/* Empty State */
.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: var(--text-secondary);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
}
</style>