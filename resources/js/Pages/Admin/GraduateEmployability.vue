<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    graduates: Array,
    filters: Object,
    batchYears: Array,
    campuses: Array,
});
const hasGraduates = computed(() => props.graduates && props.graduates.length > 0);
const activeTab = ref('list');
const batchYear = ref(props.filters.batch_year || '');
const campusId = ref(props.filters.campus_id || '');

const filter = () => {
    router.get(route('admin.graduate-employability'), {
        batch_year: batchYear.value,
        campus_id: campusId.value,
    }, {
        preserveState: true,
    });
};

const exportExcel = async () => {
    try {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = route('admin.graduate-employability.export-excel');
        
        if (batchYear.value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'batch_year';
            input.value = batchYear.value;
            form.appendChild(input);
        }
        
        if (campusId.value) {
            const campusInput = document.createElement('input');
            campusInput.type = 'hidden';
            campusInput.name = 'campus_id';
            campusInput.value = campusId.value;
            form.appendChild(campusInput);
        }
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    } catch (error) {
        if (error.response && error.response.status === 404) {
            alert('No data available for export with the current filters');
        } else {
            alert('An error occurred during export');
        }
    }
};

const exportPDF = async () => {
    try {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = route('admin.graduate-employability.export-pdf');
        
        if (batchYear.value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'batch_year';
            input.value = batchYear.value;
            form.appendChild(input);
        }
        
        if (campusId.value) {
            const campusInput = document.createElement('input');
            campusInput.type = 'hidden';
            campusInput.name = 'campus_id';
            campusInput.value = campusId.value;
            form.appendChild(campusInput);
        }
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    } catch (error) {
        if (error.response && error.response.status === 404) {
            alert('No data available for export with the current filters');
        } else {
            alert('An error occurred during export');
        }
    }
};

const getEmploymentStatusClass = (status) => {
    switch(status) {
        case 'Employed':
            return 'text-success';
        case 'Waiting for response':
            return 'text-muted';
        default:
            return '';
    }
};
</script>

<template>
    <AdminLayout title="Graduate Employability">
        <Head title="Graduate Employability" />
        
        <div class="main-content">
            <!-- Filters -->
            <div class="data-section">
  <div class="filter-grid">
            <div class="filter-group">
                <label>Batch Year</label>
                <select v-model="batchYear" @change="filter">
                    <option value="">All Batches</option>
                    <option 
                        v-for="year in batchYears" 
                        :key="year"
                        :value="year"
                    >
                        {{ year }}-{{ parseInt(year) + 1 }}
                    </option>
                </select>
            </div>
            <div class="filter-group">
                <label>Campus</label>
                <select v-model="campusId" @change="filter">
                    <option value="">All Campuses</option>
                    <option 
                        v-for="campus in campuses" 
                        :key="campus.id"
                        :value="campus.id"
                    >
                        {{ campus.campus_name }}
                    </option>
                </select>
            </div>
            <button @click="filter" class="btn btn-primary">
                Apply Filters
            </button>
            <!-- Only show export buttons when there are graduates -->
            <button 
                v-if="hasGraduates" 
                @click="exportExcel" 
                class="btn btn-success"
            >
                Export Excel
            </button>
            <button 
                v-if="hasGraduates" 
                @click="exportPDF" 
                class="btn btn-danger"
            >
                Export PDF
            </button>
        </div>
            </div>

            <!-- Tabs -->
            <div class="tab-nav">
                <button @click="activeTab = 'list'" :class="{ active: activeTab === 'list' }">
                    Graduate List
                </button>
            </div>

            <!-- Graduate List -->
            <div v-if="activeTab === 'list'" class="data-section">
                <div class="table-responsive">
                    <table class="graduate-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Year Graduated</th>
                                <th>Name</th>
                                <th>Degree</th>
                                <th>Campus</th>
                                <th>Gender</th>
                                <th>Employment Status</th>
                                <th>Company/Institution</th>
                                <th>Company Address</th>
                                <th>Position</th>
                                <th>Year Employed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(graduate, index) in graduates" :key="graduate.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ graduate.year_graduated }}-{{ parseInt(graduate.year_graduated) + 1 }}</td>
                                <td>
                                    {{ graduate.last_name }}, {{ graduate.first_name }} {{ graduate.middle_initial }}
                                </td>
                                <td>
                                    {{ graduate.degree_earned }} ({{ graduate.degree_type }})
                                    <span v-if="graduate.is_from_mindoro_state" class="badge bg-primary">
                                        MSU
                                    </span>
                                </td>
                                <td>
                                    <span v-if="graduate.campus_name" class="badge bg-secondary">
                                        {{ graduate.campus_name }}
                                    </span>
                                    <span v-else>N/A</span>
                                </td>
                                <td>{{ graduate.gender }}</td>
                                <td :class="getEmploymentStatusClass(graduate.employment_status)">
                                    {{ graduate.employment_status }}
                                </td>
                                <td>
                                    <Link v-if="graduate.company" :href="route('admin.companies.show', graduate.company.id)">
                                        {{ graduate.company.name }}
                                    </Link>
                                    <span v-else-if="graduate.employment_history && graduate.employment_history.institution">
                                        {{ graduate.employment_history.institution }}
                                    </span>
                                    <span v-else>N/A</span>
                                </td>
                                <td>
                                    <span v-if="graduate.company_address">
                                        {{ graduate.company_address }}
                                    </span>
                                    <span v-else-if="graduate.employment_history && graduate.employment_history.institution_address">
                                        {{ graduate.employment_history.institution_address }}
                                    </span>
                                    <span v-else>N/A</span>
                                </td>
                                <td>{{ graduate.job_title || 'N/A' }}</td>
                                <td>
                                    {{ graduate.first_job_year }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div v-if="graduates.length === 0" class="no-results">
                        No graduates found for the selected filters.
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.graduate-table {
    width: 100%;
    border-collapse: collapse;
}

.graduate-table th, .graduate-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.graduate-table th {
    font-weight: 600;
}

.text-success {
    color: #28a745;
}

.text-warning {
    color: #ffc107;
}

.text-muted {
    color: #6c757d;
}

.badge {
    display: inline-block;
    padding: 0.25em 0.4em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
    margin-left: 5px;
}

.no-results {
    padding: 20px;
    text-align: center;
    color: #6c757d;
    font-style: italic;
}
/* Reuse styles from your existing analytics component */
.main-content {
    padding: 20px;
    color: var(--text-primary);
}

.page-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.data-section {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

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

.tab-nav {
    display: flex;
    border-bottom: 1px solid var(--card-border);
    margin-bottom: 20px;
}

.tab-nav button {
    padding: 10px 20px;
    background: none;
    border: none;
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

option {
    background-color: var(--bg-dark);
    color: var(--text-secondary);
}

select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 5px var(--primary-light);
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
}
</style>